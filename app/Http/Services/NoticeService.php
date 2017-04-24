<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 2017/3/18
 * Time: 14:30
 */

namespace App\Http\Services;

use App\Http\Libraries\Tools;
use App\Http\Libraries\RedisKeys;
use App\Http\Models\Redis;

class NoticeService
{
    private $redis;

    public function __construct()
    {
        $this->redis = (new Redis())->getClient();
    }

    public function insertNotice($uid, $notice)
    {
        //生成 notice ID
        $key = RedisKeys::ZSET_NOTICES.$uid.':nextid';
        $id = (int)$this->redis->get($key);
        $this->redis->incr($key);
        $this->redis->expire($key,RedisKeys::CACHE_EXPIRED_TIME);

        //生成 notice
        $key = RedisKeys::ZSET_NOTICES.$uid.':'.$id;
        $this->redis->hset($key,'id',$id);
        while($subkey = key($notice)){
            $this->redis->hset($key,$subkey,$notice[$subkey]);
            next($notice);
        }
        $this->redis->expire($key,RedisKeys::CACHE_EXPIRED_TIME);

        //加入 notice 集合
        $key = RedisKeys::ZSET_NOTICES.$uid.':all';
        $this->redis->zadd($key, time() , $id);
        $count = $this->redis->zcard($key);
        $num = $count - 1000;
        if( $num>0 )$this->redis->zremrangebyrank($key,0,$num-1);
        $this->redis->expire($key,RedisKeys::CACHE_EXPIRED_TIME);
    }


    public function getNotices($uid)
    {
        $key = RedisKeys::ZSET_NOTICES.$uid.':all';
        $notices = $this->redis->zrevrange($key,0,-1);

        $res = [];
        foreach ($notices as $id){
            $key = RedisKeys::ZSET_NOTICES.$uid.':'.$id;
            $notice = $this->redis->hgetall($key);
            if($notice)$res []= $notice;
        }
        return $res;
    }

    public function readNotice($uid,$id)
    {
        $key = RedisKeys::ZSET_NOTICES.$uid.':'.$id;
        $this->redis->hset($key,'is_read',1);
    }

    public function clearNotices($uid)
    {
        $key = RedisKeys::ZSET_NOTICES.$uid.':all';
        $notices = $this->redis->zrevrange($key,0,-1);

        foreach ($notices as $id){
            $key = RedisKeys::ZSET_NOTICES.$uid.':'.$id;
            $this->redis->hset($key,'is_read',1);
        }
    }
}