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
    private $_uid=0;
    private $_username='';
    private $_picture='';

    public function getUserInfo($uid)
    {
        //TODO Redis cache
        $res = Users::select(['id as uid','username','picture'])
            ->where('id',$uid)->first();
        return $res ? $res->toArray() : [];
    }

    public function updateUserInfo()
    {
        $user = [
            'username'=>$this->_username,
            'picture' =>$this->_picture,
            'update_time'=>time()
        ];
        return Users::where('id',$this->_uid)->update($user);
    }

    public function checkPassword($old)
    {
        return Users::where([
            'id'=>$this->_uid,
            'password'=>$old,
        ])->exists();
    }

    public function changePassword($new)
    {
        return Users::where('id',$this->_uid)
            ->update([
                'password'   =>$new,
                'update_time'=>time(),
            ]);
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->_uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->_uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->_username = $username;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->_picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->_picture = $picture;
    }


}