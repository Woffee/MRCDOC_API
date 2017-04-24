<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/21
 * Time: 10:09
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FriendService;
use App\Http\Services\UserService;
use App\Http\Services\NoticeService;

class FriendController extends Controller
{
    /**
     * 我的好友列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs, [
            'uid' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];

        $friendService = new FriendService();
        $friendService->setUid( $uid );
        $friends = $friendService->getMyFriends();

        return $this->success( ['friends'=>$friends] );
    }

    /**
     * Search friends by user name
     *
     * @param $search
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchFriend( $search )
    {
        $friendService = new FriendService();
        $friends = $friendService->searchFriend($search);
        return $this->success( ['friends'=>$friends] );
    }


    public function addFriend(Request $request)
    {
        $inputs = $request->only('uid','fid');
        $validator = app('validator')->make($inputs, [
            'uid' => 'required',
            'fid' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $fid = (int)$inputs['fid'];

        $friendService = new FriendService();

        if($friendService->isFriends($uid, $fid)){
            return $this->error('你们已经是好友关系');
        }

        $friendService->setFromUid( $uid );
        $friendService->setToUid( $fid );
        $friendService->setStatus( 0 );
        $res = $friendService->addFriend();

        if( !$res ){
            return $this->error('添加好友失败');
        }

        $userService = new UserService();
        $userInfo = $userService->getUserInfo($uid);

        //向被申请人发出通知
        $noticeService = new NoticeService();
        $notice = [
            'type'=>0,
            'message'=>$userInfo['username'].'申请你为好友',
            'from_uid'=>$uid,
            'from_username'=>$userInfo['username'],
            'from_picture'=>$userInfo['picture'],
            'create_time'=>time(),
        ];
        $noticeService->insertNotice($fid, $notice);

        return $this->success();
    }

    public function replyFriend(Request $request)
    {
        $inputs = $request->only('uid','fid','is_accept');
        $validator = app('validator')->make($inputs, [
            'uid' => 'required',
            'fid' => 'required',
            'is_accept' => 'required|integer|min:0|max:1',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $fid = (int)$inputs['fid'];
        $isAccept = (int)$inputs['is_accept'];

        $friendService = new FriendService();
        $friendService->setUid( $uid );
        $friendService->setFid( $fid );
        $res = $friendService->replyFriend($isAccept);

        if( !$res ){
            return $this->error('同意或拒绝好友失败');
        }

        //向申请人发出通知
        $noticeService = new NoticeService();
        $userService = new UserService();
        $userInfo = $userService->getUserInfo($fid);
        $notice = [
            'type'=>2,  //对方同意了你的好友申请
            'from_uid'=>$fid,
            'from_username'=>$userInfo['username'],
            'from_picture'=>$userInfo['picture'],
            'create_time'=>time(),
        ];
        $noticeService->insertNotice($fid, $notice);

        return $this->success();
    }

    public function deleteFriend(Request $request)
    {
        $inputs = $request->only('uid','fid');
        $validator = app('validator')->make($inputs, [
            'uid' => 'required',
            'fid' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $fid = (int)$inputs['fid'];

        $friendService = new FriendService();
        $friendService->setUid( $uid );
        $friendService->setFid( $fid );
        $res = $friendService->deleteFriend();

        if( !$res ){
            return $this->error('删除好友失败');
        }
        return $this->success();
    }

}