<?php
/**
 * Created by PhpStorm.
 * User: Woffee
 * Date: 2017/3/18
 * Time: 14:30
 */

namespace App\Http\Services;

use App\Http\Models\Comments;
use App\Http\Services\UserService;
use App\Http\Libraries\Tools;

class CommentService
{

    private $_fileId;
    private $_uid;
    private $_content;

    public function insertComment()
    {
        $comment = [
            'file_id' => $this->_fileId,
            'uid' => $this->_uid,
            'content' => $this->_content,
            'create_time'=>time(),
            'update_time'=>time(),
        ];
        return Comments::insert($comment);
    }

    public function getComments($fileId)
    {
        $comments = Comments::select(['id as cid','uid','content','create_time'])
            ->where([
                'file_id'=>$fileId,
                'is_del' =>0,
            ])
            ->get();
        $comments = $comments ? $comments->toArray() : [];

        $userService = new UserService();

        $count = count($comments);
        for($i=0; $i<$count; $i++){
            $userInfo = $userService->getUserInfo( $comments[$i]['uid'] );
            $comments[$i]['username'] = $userInfo['username'];
            $comments[$i]['picture']  = $userInfo['picture'];

            $comments[$i]['create_time']  = Tools::human_time_diff( $comments[$i]['create_time'] );
        }
        return $comments;
    }

    public function deleteComment($cid)
    {
        return Comments::where('id',$cid)
            ->update([
                'is_del'=>1,
                'update_time'=>time(),
            ]);
    }

    public function checkComment($cid,$uid)
    {
        return Comments::where([
            'id'=>$cid,
            'uid'=>$uid,
        ])->exists();
    }


    /**
     * @return mixed
     */
    public function getFileId()
    {
        return $this->_fileId;
    }

    /**
     * @param mixed $fileId
     */
    public function setFileId($fileId)
    {
        $this->_fileId = $fileId;
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
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }



}