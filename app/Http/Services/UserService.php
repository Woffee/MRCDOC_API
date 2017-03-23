<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 2017/3/18
 * Time: 14:30
 */

namespace App\Http\Services;

use App\Http\Models\Files;
use App\Http\Models\Users;

class UserService
{

    public function getUserInfo($uid)
    {
        //TODO Redis cache
        $res = Users::select(['id as uid','username','picture'])
            ->where('id',$uid)->first();
        return $res ? $res->toArray() : [];
    }

}