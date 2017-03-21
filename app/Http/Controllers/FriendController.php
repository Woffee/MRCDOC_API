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
use App\Http\Models\Users;
use App\Http\Models\Friending;
use App\Http\Models\Friends;

class FriendController extends Controller
{
    public function friends(Request $request)
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
        $friendService->setFromUid( $uid );
        $friendService->setToUid( $fid );
        $friendService->setStatus( 0 );
        $res = $friendService->addFriend();

        if( !$res ){
            return $this->error('添加好友失败');
        }
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