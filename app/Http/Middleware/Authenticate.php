<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Models\Redis;
use App\Http\Libraries\Tools;
use App\Http\Libraries\RedisKeys;

class Authenticate
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $token = $request->header('token');

        if( empty($token) ){
            return $this->error('token 不能为空');
        }

        $uid = Tools::getUserIdByToken($token);
        if( empty($uid) ){
            return $this->error('token 验证失败');
        }

        $redis = (new Redis())->getClient();
        $keys = RedisKeys::TOKEN.$uid;
        if( !env('APP_DEBUG') && $token != $redis->get($keys) ){
            return $this->error('token 验证失败');
        }

        $request->merge(['uid'=>$uid]);
        $request->merge(['token'=>$token]);

        return $next($request);
    }

    protected function error($message = '')
    {
        return response()->json([
            'status_code'   =>   240,
            'message'       =>    $message,
        ]);
    }
}
