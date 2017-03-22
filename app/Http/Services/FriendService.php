<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/21
 * Time: 10:34
 */

namespace App\Http\Services;

use App\Http\Services\UserService;
use App\Http\Models\Users;
use App\Http\Models\Friending;
use App\Http\Models\Friends;
use DB;

class FriendService
{
    private $_uid;
    private $_fid;

    private $_fromUid;
    private $_toUid;
    private $_status = 0;

    /** 0:发起邀请  1:已接受   2:已拒绝 */

    public function getMyFriends()
    {
        $friends = Friends::select(['fid'])->where('uid', $this->_uid)->get();
        $friends = $friends ? $friends->toArray() : [];

        $userService = new UserService();
        for( $i =0 ; $i<count($friends) ; $i++ ){
            $user = $userService->getUserInfo($friends[$i]['fid']);
            $friends[$i]['username'] = $user['username'];
            $friends[$i]['picture'] = $user['picture'];
        }

        return $friends;
    }

    public function isFriends($uid,$fid)
    {
        return Friends::where([
            'uid'=>$uid,
            'fid'=>$fid,
            'is_del' =>0,
        ])->exists();
    }

    /**
     * 添加好友记录
     *    注意：申请好友记录，无论对方同意或拒绝都给予保留
     *
     * @return bool
     */
    public function addFriend()
    {
        $friend = Friends::where('uid', $this->_fromUid)
            ->where('fid', $this->_toUid)
            ->where('is_del', 0)
            ->first();
        if ($friend) return false;
        /**已经是好友了*/

        $friending = Friending::where('from_uid', $this->_fromUid)
            ->where('to_uid', $this->_toUid)
            ->where('status',0)
            ->first();

        if ( empty($friending) ) {
            $friending = [
                'from_uid' => $this->_fromUid,
                'to_uid' => $this->_toUid,
                'status' => $this->_status,
                'create_time' => time(),
                'update_time' => time(),
            ];
            return Friending::insert($friending);
        }
        return true;
    }

    public function replyFriend($isAccept)
    {
        DB::beginTransaction();

        $res = Friending::where('to_uid', $this->_uid)
            ->where('status', 0)
            ->update(['status' => $isAccept ? 1 : 2]);

        /** 修改失败 */
        if( !$res ){
            DB::rollback();
            return false;
        }

        /** 拒绝操作，不用添加好友 */
        if( !$isAccept ){
            DB::commit();
            return true;
        }


        /** 添加好友（双向） */
        $friend = [
            'uid' => $this->_uid,
            'fid' => $this->_fid,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $res = $this->insertOrUpdateFriend($friend);
        if (  !$res ) {
            DB::rollback();
            return false;
        }
        $friend = [
            'uid' => $this->_fid,
            'fid' => $this->_uid,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $res = $this->insertOrUpdateFriend($friend);
        if (  !$res ) {
            DB::rollback();
            return false;
        }

        DB::commit();
        return true;

    }

    public function searchFriend($search)
    {
        $friends = Users::select(['uid', 'username', 'picture'])->where('username', 'like', '%' . $search . '%');
        $friends = $friends ? $friends->toArray() : [];
        return $friends;
    }


    public function deleteFriend()
    {
        return Friends::where('uid', $this->_uid)
            ->where('fid', $this->_fid)
            ->update(['is_del' => 1, 'update_time' => time()]);
    }

    public function insertOrUpdateFriend($friend = [])
    {
        $res = Friends::where('uid',$friend['uid'])
            ->where('fid',$friend['fid'])
            ->first();
        if( empty($res) )return Friends::insert($friend);

        $res = $res->toArray();
        if( $res['is_del']==0 )return true;
        else{
            $update = [
                'is_del'=>0,
                'update_time' => time(),
            ];
            return Friends::where('uid',$friend['uid'])
                ->where('fid',$friend['fid'])
                ->update($update);
        }
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
    public function getFid()
    {
        return $this->_fid;
    }

    /**
     * @param mixed $fid
     */
    public function setFid($fid)
    {
        $this->_fid = $fid;
    }

    /**
     * @return mixed
     */
    public function getFromUid()
    {
        return $this->_fromUid;
    }

    /**
     * @param mixed $fromUid
     */
    public function setFromUid($fromUid)
    {
        $this->_fromUid = $fromUid;
    }

    /**
     * @return mixed
     */
    public function getToUid()
    {
        return $this->_toUid;
    }

    /**
     * @param mixed $toUid
     */
    public function setToUid($toUid)
    {
        $this->_toUid = $toUid;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }




}