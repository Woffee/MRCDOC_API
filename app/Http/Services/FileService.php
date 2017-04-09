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
use App\Http\Services\UserService;
use App\Http\Libraries\Tools;
use function FastRoute\TestFixtures\empty_options_cached;
use Illuminate\Http\File;

class FileService
{
    private $_file_id;
    private $_filename;
    private $_creator;
    private $_content ='{"ops":[{"insert":"\u0068\u0065\u006c\u006c\u006f\u002c\u0020\u8fd9\u662f\u4f60\u7684\u79c1\u6709\u0070\u0061\u0064\uff0c\u4f60\u53ef\u4ee5\u628a\u0075\u0072\u006c\u5206\u4eab\u7ed9\u4f60\u7684\u5c0f\u4f19\u4f34\n"}]}';
    private $_type = 1;
    private $_in_folder = 'desk';

    public function isCreator($fileId,$uid)
    {
        return Files::where([
            'file_id'=>$fileId,
            'creator'=>$uid,
            'status' =>0,
        ])->exists();
    }

    public function getFileInfo($uid,$fileId)
    {
        $file = Files::where('file_id',$fileId)
            ->where('uid' , $uid)
            ->where('status' , 0)
            ->where('type','<>', 0)
            ->first();
        $file = $file ? $file->toArray() : [];

        return [
             'file_id'         =>$file['file_id'],
             'filename'        =>$file['filename'],
             'creator_id'      =>$file['creator'],
             'creator_name'    =>'',
             'creator_picture' =>'',
             'type'            =>$file['type'],
             'content'         =>$file['content'],
             'create_time'     =>Tools::human_time_diff($file['create_time']),
             'update_time'     =>Tools::human_time_diff($file['update_time']),
        ];
    }

    public function getFilesCountInFolder($uid,$folderId)
    {
        return Files::where('in_folder',$folderId)
            ->where('uid' , $uid)
            ->where('status',0)
            ->count();
    }

    public function getFilesByFolderId($uid, $folderId = 'desk',$page = 1,$pageSize = 10)
    {
        $limit = (int)($page - 1) * $pageSize;
        $offset = !empty($pageSize) ? (int)$pageSize : 10;

        $files = Files::where('in_folder',$folderId)
            ->where('uid' , $uid)
            ->where('status',0)
            ->skip($limit)
            ->take($offset)
            ->get();

        $files =  $files ? $files->toArray() : [];
        $res = [];
        $userService = new UserService();
        foreach ($files as $file){
            $userInfo = $userService->getUserInfo($file['creator']);
            if(empty($userInfo['picture']))$userInfo['picture'] = 'https://pic2.zhimg.com/33a85ab39e985ab6823ad93de0b826f5_im.jpg';
            $res []= [
                'file_id'         =>$file['file_id'],
                'filename'        =>$file['filename'],
                'creator_id'      =>$file['creator'],
                'creator_name'    =>$userInfo['username'],
                'creator_picture' =>$userInfo['picture'],
                'type'            =>$file['type'],
                'content'         =>$file['content'],
                'create_time'     =>Tools::human_time_diff($file['create_time']),
                'update_time'     =>Tools::human_time_diff($file['update_time']),
            ];
        }
        return $res;
    }

    public function createFileGetId()
    {
        $file_id = Tools::generateID();
        $file = [
            'uid'       => $this->_creator,
            'file_id'   => $file_id,
            'filename'  => $this->_filename,
            'creator'   => $this->_creator,
            'content'   => $this->_content,
            'type'      => $this->_type,
            'in_folder' => $this->_in_folder,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $res =  Files::create($file);
        return $res ? $file_id : '';
    }

    public function updateFile()
    {
        $file = [
            'filename'  => $this->_filename,
            'creator'   => $this->_creator,
            'content'   => $this->_content,
            'update_time' => time(),
        ];
        $res =  Files::where('file_id', $this->_file_id )
            ->where('status' , 0)
            ->update($file);
        return $res ? true : false;
    }

    public function moveFiles($uid=0, $strFileIds='', $moveTo = 'desk')
    {
        $fileIds = $this->explodeFileIds($strFileIds);

        if( $moveTo != 'desk' && empty( Files::where([
                'file_id'=> $moveTo,
                'type' => 0
            ])) ){
            return false;
        }

        $res =  Files::where('creator',$uid)
            ->where('status' , 0)
            ->whereIn('file_id', $fileIds )
            ->update(['in_folder'=>$moveTo]);
        return $res ? true : false;
    }

    public function deleteFiles($uid=0, $strFileIds='')
    {
        $fileIds = $this->explodeFileIds($strFileIds);

        $res =  Files::where('creator',$uid)
            ->whereIn('file_id', $fileIds )
            ->update(['status'=> 1]);
        return $res ? true : false;
    }

    public function explodeFileIds($strFileIds)
    {
        $arr = explode(',',$strFileIds);
        $res = [];
        foreach ($arr as $one) {
            if(!empty($one)) $res []= $one;
        }
        return $res;
    }

    public function getRecycleFiles( $uid )
    {
        $files = Files::select(['file_id','filename','update_time'])
            ->where('uid' , $uid)
            ->where('status',1)
            ->get();
        return $files ? $files->toArray() : [];
    }

    public function restoreFile( $fileId, $uid )
    {
        return Files::where([
            'uid'=>$uid,
            'file_id'=>$fileId
        ])->update(['status'=>0]);
    }

    /**
     * @return mixed
     */
    public function getFileId()
    {
        return $this->_file_id;
    }

    /**
     * @param mixed $file_id
     */
    public function setFileId($file_id)
    {
        $this->_file_id = $file_id;
    }




    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->_creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->_creator = $creator;
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

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * @return mixed
     */
    public function getInFolder()
    {
        return $this->_in_folder;
    }

    /**
     * @param mixed $in_folder
     */
    public function setInFolder($in_folder)
    {
        $this->_in_folder = $in_folder;
    }



}