<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/21
 * Time: 15:38
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FileService;
use App\Http\Services\WriterService;
use App\Http\Services\FriendService;
use App\Http\Models\Writers;

class WriterController extends Controller
{
    /**
     * 获取文档的所有协作者列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function writers(Request $request)
    {
        $inputs = $request->only('uid','file_id');
        $validator = app('validator')->make($inputs, [
            'uid' => 'required',
            'file_id' => 'required',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $fileId = $inputs['file_id'];

        $writerService = new WriterService();
        if( !$writerService->isWriter($fileId,$uid) ){
            return $this->error('权限不够：您不是该文档的协作者');
        }

        $writers = $writerService->getWritersOfDoc($fileId);

        return $this->success(['writers'=>$writers]);
    }

    /**
     * 创建协作者
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createWriter(Request $request)
    {
        $inputs = $request->only('uid','file_id','writer_ids');
        $validator = app('validator')->make($inputs, [
            'uid'        => 'required',
            'file_id'    => 'required',
            'writer_ids'  => 'required',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $fileId = $inputs['file_id'];
        $writerIds = $inputs['writer_ids'];

        $writerService = new WriterService();
        $fileService = new FileService();
        $friendService = new FriendService();

        if( !$fileService->isCreator($fileId,$uid) ){
            return $this->error('权限不够：您不是该文档的创建者');
        }

        $fileInfo = $fileService->getFileBaseInfo($fileId);

        $wids = $this->explodeUids($writerIds);
        $num=0;
        foreach ($wids as $wid){
            if( !$writerService->isWriter($fileId,$wid) )continue;
            if( !$friendService->isFriends( $uid,$wid ) )continue;
            $num++;
            $writerService->createWriter($fileId,$wid);
            $fileService->createFileOfWriter($wid,$fileInfo);
        }


        return $this->success(['count'=>$num]);
    }

    /**
     * 删除协作者
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteWriter(Request $request)
    {
        $inputs = $request->only('uid','file_id','writer_id');
        $validator = app('validator')->make($inputs, [
            'uid'        => 'required',
            'file_id'    => 'required',
            'writer_id'  => 'required',
        ], ['required' => ':attribute 不能为空']);
        if ($validator->fails()) {
            return $this->error($validator->errors()->all());
        }

        $uid = (int)$inputs['uid'];
        $fileId = $inputs['file_id'];
        $writerId = $inputs['writer_id'];

        $fileService = new FileService();
        if( !$fileService->isCreator($fileId,$uid) ){
            return $this->error('权限不够：您不是该文档的创建者');
        }

        $writerService = new WriterService();
        $res = $writerService->deleteWriter($fileId,$writerId);
        if( !$res ){
            return $this->error('删除失败');
        }

        return $this->success();
    }



    public function explodeUids($strUids)
    {
        $arr = explode(',',$strUids);
        $res = [];
        foreach ($arr as $one) {
            if(!empty($one)) $res []= $one;
        }
        return $res;
    }
}