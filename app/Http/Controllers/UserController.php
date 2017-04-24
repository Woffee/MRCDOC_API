<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/23
 * Time: 9:53
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FriendService;
use App\Http\Services\UserService;
use App\Http\Models\Users;
use App\Http\Models\Friending;
use App\Http\Models\Friends;

class UserController extends Controller
{

    /**
     * 用户个人信息
     *
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

        $userService = new UserService();
        $userInfo = $userService->getUserInfo($uid);

//        $friendService = new FriendService();
//        $friendings = $friendService->getMyFriendings($uid);

        return $this->success([
            'userinfo'=>$userInfo,
            //'new_friends_applications'=>$friendings,
        ]);
    }

    /**
     * Update user information
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $inputs = $request->only('uid','username','picture');
        $validator = app('validator')->make($inputs, [
            'uid'     => 'required',
            'username'=> 'required',
            'picture' => 'required'
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $username = $inputs['username'];
        $picture  = $inputs['picture'];

        $userService = new UserService();
        $userService->setUid($uid);
        $userService->setUsername($username);
        $userService->setPicture($picture);
        if( $userService->updateUserInfo()){
            return $this->success('修改成功');
        }else{
            return $this->error('修改失败');
        }
    }


    public function changePassword(Request $request)
    {
        $inputs = $request->only('uid','password_old','password_new');
        $validator = app('validator')->make($inputs, [
            'uid'     => 'required',
            'password_old' => 'required',
            'password_new' => 'required'
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $old = (string)$inputs['password_old'];
        $new = (string)$inputs['password_new'];

        if( $old == $new ){
            $this->error('修改失败：新旧密码不能一致');
        }

        $userService = new UserService();
        $userService->setUid($uid);

        if( !$userService->checkPassword($old)){
            return $this->error('修改失败：原密码错误');
        }

        if( $userService->changePassword($new) ){
            return $this->success();
        }else{
            return $this->error('修改失败');
        }
    }
}