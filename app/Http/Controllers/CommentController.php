<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\CommentService;

class CommentController extends Controller
{


    /**
     * 评论列表
     *
     * @param $fileId
     * @return \Illuminate\Http\JsonResponse
     * @internal param Request $request
     */
    public function commentList($fileId)
    {
        $commentService = new CommentService();
        $comments = $commentService->getComments($fileId);

        return $this->success( ['comments'=>$comments] );
    }

    /**
     * 创建评论
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createComment(Request $request)
    {
        $inputs = $request->only('uid','file_id','content');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
            'file_id'    =>    'required',
            'content'    =>    'required',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $fileId = $inputs['file_id'];
        $content = $inputs['content'];

        $commentService = new CommentService();
        $commentService->setFileId($fileId);
        $commentService->setUid($uid);
        $commentService->setContent($content);

        if( !$commentService->insertComment() ){
            return $this->error('评论失败');
        }
        return $this->success();
    }

    /**
     * Delete Comment
     *
     * @param $cid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment($cid,Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        $commentService = new CommentService();
        if( !$commentService->checkComment($cid,$uid) ){
            return $this->error('删除失败：你不是该评论的拥有者');
        }

        if( !$commentService->deleteComment($cid) ){
            return $this->error('删除失败');
        }
        return $this->success();
    }

}
