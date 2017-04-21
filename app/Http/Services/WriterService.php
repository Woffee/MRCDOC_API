<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/22
 * Time: 9:31
 */

namespace App\Http\Services;

use App\Http\Models\Writers;
use App\Http\Services\UserService;

class WriterService
{
    public function isWriter($docId,$uid)
    {
        return Writers::where([
            'file_id'=>$docId,
            'uid'=>$uid,
            'is_del'=>0,
        ])->exists();
    }

    public function getWritersOfDoc($docId)
    {
        $writers = Writers::select(['file_id','uid as writer_id'])
            ->where([
            'file_id'=>$docId,
            'is_del'=>0,
        ])->get();
        $writers = $writers ? $writers->toArray() : [];
        $userService = new UserService();

        for($i=0; $i<count($writers); $i++){
            $userInfo = $userService->getUserInfo($writers[$i]['writer_id']);
            $writers[$i]['writer_name'] = $userInfo['username'];
            $writers[$i]['writer_picture']  = $userInfo['picture'];
        }
        return $writers;
    }

    /**
     * 创建协作者
     * @param $docId
     * @param $uid
     * @return mixed
     */
    public function createWriter($docId,$uid)
    {
        $writer = [
              'file_id' => $docId,
              'uid' => $uid,
        ];
        return $this->insertOrUpdate($writer);
    }

    public function deleteWriter($docId,$uid)
    {
        return Writers::where([
            'file_id'=>$docId,
            'uid'=>$uid,
        ])->update([
            'is_del'=>1,
            'update_time'=>time(),
        ]);
    }

    public function insertOrUpdate($writer)
    {
        if( !Writers::where($writer)->exists() ){
            $writer['is_del']=0;
            $writer['create_time']=time();
            $writer['update_time']=time();
            return Writers::insert($writer);
        }else{
            $update['is_del']=0;
            $update['update_time']=time();
            return Writers::where($writer)->update($update);
        }
    }

}