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
        $key = $this->getKey( $uid );
        $this->redis->zadd($key, time() ,json_encode($notice));
        $key = $this->getCountKey($uid);
        $this->redis->incr($key);
    }

    public function getCount($uid)
    {
        $key = $this->getCountKey($uid);
        return (int)$this->redis->get($key);
    }

    public function getNotices($uid)
    {
        $key = $this->getKey($uid);
        $notices = $this->redis->zrevrange($key,0,-1);

        $count = $this->getCount($uid);
        $res = [];
        $i = 0;
        foreach ($notices as $notice){
            $arr = json_decode( $notice ,true);
            $arr['is_read'] = $i<$count ? 0 : 1;
            $arr['create_time'] = Tools::human_time_diff( $arr['create_time'] );
            $res []= $arr;
            $i++;
        }
        return $res;
    }

    public function readNotices($uid)
    {
        $key = $this->getCountKey($uid);
        $this->redis->set($key,0);
    }

    public function getKey( $uid )
    {
        return RedisKeys::ZSET_NOTICES.$uid;
    }

    public function getCountKey($uid)
    {
        return RedisKeys::ZSET_NOTICES.$uid.':count';
    }
}