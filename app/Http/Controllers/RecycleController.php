<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/24
 * Time: 14:44
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FileService;

class RecycleController extends Controller
{
    /**
     * 回收站文件列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function files( Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        $fileService = new FileService();
        $files = $fileService->getRecycleFiles($uid);


        return $this->success(['files'=>$files]);
    }

    /**
     * 还原文件
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore( Request $request)
    {
        $inputs = $request->only('uid','file_id');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
            'file_id'   =>    'required',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $fileId = $inputs['file_id'];

        $fileService = new FileService();
        $res = $fileService->restoreFile($fileId,$uid);

        if( !$res ){
            return $this->error('还原失败');
        }
        return $this->success();
    }
}