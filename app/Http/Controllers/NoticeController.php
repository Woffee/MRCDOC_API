<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\NoticeService;

class NoticeController extends Controller
{


    /**
     * 通知列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticesList(Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        $noticeService = new NoticeService();
        $notices = $noticeService->getNotices($uid);

        return $this->success( [
            'count'=>count($notices),
            'notices'=>$notices
        ] );
    }

    /**
     * 阅读通知
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function readNotice($id,Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        $noticeService = new NoticeService();
        $noticeService->readNotice($uid,$id);

        return $this->success( );
    }

}
