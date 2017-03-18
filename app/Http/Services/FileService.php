<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 2017/3/18
 * Time: 14:30
 */

namespace App\Http\Services;

use App\Http\Models\Files;
use App\Http\Models\Tools;

class FileService
{
    private $_file_id;
    private $_filename;
    private $_creator;
    private $_content ='';
    private $_type = 1;
    private $_in_folder = 'desk';

    public function getFileInfo($fileId)
    {
        $file = Files::where('file_id',$fileId)
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
             'create_time'     =>$file['create_time'],
             'update_time'     =>$file['update_time'],
        ];
    }

    public function getFilesByFolderId( $folderId = 'desk')
    {
        $files = Files::where('in_folder',$folderId)
            ->where('status',0)
            ->get();
        $files =  $files ? $files->toArray() : [];
        $res = [];
        foreach ($files as $file){
            $res []= [
                'file_id'         =>$file['file_id'],
                'filename'        =>$file['filename'],
                'creator_id'      =>$file['creator'],
                'creator_name'    =>'',
                'creator_picture' =>'',
                'type'            =>$file['type'],
                'content'         =>$file['content'],
                'create_time'     =>$file['create_time'],
                'update_time'     =>$file['update_time'],
            ];
        }
        return $res;
    }

    public function createFileGetId()
    {
        $file_id = Tools::generateID();
        $file = [
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

    public function moveFiles()
    {

    }

    public function deleteFiles()
    {

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