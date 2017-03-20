<?php
/**
 * AuthController.php
 *
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Models\Users;
use App\Http\Models\Redis;
use App\Http\Libraries\Tools;
use App\Http\Libraries\RedisKeys;

class AuthController extends Controller
{
    /**
     * 用户登录
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $inputs = $request->only('username', 'password');
        $validator = app('validator')->make($inputs, [
            'username' => 'required',
            'password' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if($validator->fails()){
            return $this->error( $validator->errors()->all() );
        }

        $username = $inputs['username'];
        $password = $inputs['password'];

        $user = Users::where('username',$username)->first();

        if( !$user ){
            return $this->error('用户名不存在');
        }

        if( $password != $user->password ){
            return $this->error('密码错误');
        }

        $token = Tools::generateToken( $user->id );

        /** 存到Redis */
        $redis = (new Redis())->getClient();
        $tokenKey = RedisKeys::TOKEN.$user->id;
        $redis->set($tokenKey,$token);
        $redis->expire($tokenKey, RedisKeys::CACHE_EXPIRED_TIME);

        return $this->success([ 'token'=>$token ]);
    }


    /**
     * 第三方登录
     * @param Request $request
     */
    public function oauth(Request $request)
    {

    }


    /**
     * 注册
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        $inputs = $request->only('username', 'password');
        $validator = app('validator')->make($inputs, [
            'username' => 'required',
            'password' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if($validator->fails()){
            return $this->error( $validator->errors()->all() );
        }


        $username = $inputs['username'];
        $password = $inputs['password'];

        if( Users::where('username',$username)->first() ){
            return $this->error('用户名已存在');
        }

        $user = [
            'username' => $username,
            'password' => $password,
            'register_ip' => $request->ip(),
            'create_time'=>time(),
            'update_time'=>time(),
        ];

        $uid = Users::insertGetId($user);

        if(  !$uid  ){
            return $this->error('注册失败');
        }
        return $this->success( ['uid'=>$uid ] );
    }

    /**
     * 退出登录
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs, [
            'uid' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if($validator->fails()){
            return $this->error( $validator->errors()->all() );
        }

        $uid = $inputs['uid'];

        /** 删除token */
        $redis = (new Redis())->getClient();
        $keys = RedisKeys::TOKEN.$uid;
        $redis->del($keys);


        return $this->success('退出登录成功');
    }

}