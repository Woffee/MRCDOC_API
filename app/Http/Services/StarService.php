<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 2017/3/18
 * Time: 14:30
 */

namespace App\Http\Services;

use App\Http\Controllers\UserController;
use App\Http\Models\Files;
use App\Http\Models\Stars;
use App\Http\Services\UserService;
use App\Http\Libraries\Tools;

class StarService
{

    public static function isStar($uid, $fileId)
    {
        return (int)Stars::where([
            'file_id'=>$fileId,
            'uid'=>$uid,
            'is_del' =>0,
        ])->exists();
    }

    public function starFile($uid, $fileId)
    {
        $star = [
            'uid' => $uid,
            'file_id' => $fileId,
            'update_time' => time(),
            'create_time' => time(),
        ];
        return $this->insertOrUpdate($star);
    }

    public function unStarFile($uid, $fileId)
    {
        if( !self::isStar($uid,$fileId) )return true;

        $update = [
            'is_del'=>1,
            'update_time' => time(),
        ];
        return Stars::where('uid',$uid)
            ->where('file_id',$fileId)
            ->update($update);
    }


    public function getStarFiles($uid, $page = 1, $pageSize = 10)
    {
        $limit = (int)($page - 1) * $pageSize;
        $offset = !empty($pageSize) ? (int)$pageSize : 10;

        $stars = Stars::select('file_id')
            ->where([
                'uid' => $uid,
                'is_del' => 0,
            ])
            ->get();
        $stars =  $stars ? $stars->toArray() : [];

        $count = Files::where('status',0)
            ->whereIn('file_id',$stars)
            ->count();

        $files = Files::where('status',0)
            ->whereIn('file_id',$stars)
            ->skip($limit)
            ->take($offset)
            ->get();

        $res = [];
        $userService = new UserService();
        foreach ($files as $file){
            $userInfo = $userService->getUserInfo($file['creator']);
            $res []= [
                'file_id'         =>$file['file_id'],
                'filename'        =>$file['filename'],
                'creator_id'      =>$file['creator'],
                'creator_name'    =>$userInfo['username'],
                'creator_picture' =>$userInfo['picture'],
                'type'            =>$file['type'],
                'is_star'         =>self::isStar($uid,$file['file_id']),
                'content'         =>$file['content'],
                'create_time'     =>Tools::human_time_diff($file['create_time']),
                'update_time'     =>Tools::human_time_diff($file['update_time']),
            ];
        }
        return [
            'count' => $count,
            'star_files' => $res,
        ];
    }

    /**
     * 插入一条数据，如果已存在，那就更新
     * @param array $star
     * @return bool
     */
    public function insertOrUpdate($star = [])
    {
        $res = Stars::where('uid',$star['uid'])
            ->where('file_id',$star['file_id'])
            ->first();
        if( empty($res) )return Stars::insert($star);

        $res = $res->toArray();
        if( $res['is_del']==0 )return true;
        else{
            $update = [
                'is_del'=>0,
                'update_time' => time(),
            ];
            return Stars::where('uid',$star['uid'])
                ->where('file_id',$star['file_id'])
                ->update($update);
        }
    }
}