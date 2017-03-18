<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FileService;

class FileController extends Controller
{
    public function index($file_id, Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        $file = (new FileService())->getFileInfo($file_id);

        //TODO 检查当前用户是否拥有查看权限

        return $this->success(['file'=>$file]);
    }

    public function desk(Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $files = (new FileService())->getFilesByFolderId();

        return $this->success(['files'=>$files]);
    }

    public function folder($folder_id)
    {
        $files = (new FileService())->getFilesByFolderId($folder_id);
        return $this->success($files);
    }


    public function createFile(Request $request)
    {
        $inputs = $request->only('uid','filename','type','in_folder');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
            'filename'   =>    'required',
            'type'       =>    'required|integer|min:0|max:1',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $filename = $inputs['filename'];
        $type = $inputs['type'];
        $inFolder = !empty($inputs['in_folder']) ? $inputs['in_folder'] : 'desk';

        $fileService = new FileService();
        $fileService->setCreator($uid);
        $fileService->setFilename($filename);
        $fileService->setInFolder($inFolder);
        $fileService->setType($type);

        $file_id = $fileService->createFileGetId();
        if( empty($file_id) ){
            return $this->error('创建文件失败');
        }
        return $this->success(['file_id'=>$file_id]);
    }

    public function updateFile(Request $request)
    {
        $inputs = $request->only('uid','file_id','filename');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
            'file_id'    =>    'required',
            'filename'   =>    'required',
            'content'    =>    'required',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $file_id  = $inputs['file_id'];
        $filename = $inputs['filename'];
        $content  = $inputs['filename'];

        $fileService = new FileService();
        $fileService->setCreator($uid);
        $fileService->setFileId($file_id);
        $fileService->setFilename($filename);
        $fileService->setContent($content);

        $res = $fileService->updateFile();
        if( empty($res) ){
            return $this->error('修改文件失败');
        }
        return $this->success();
    }

    public function moveFiles(Request $request)
    {

    }

    public function deleteFiles(Request $request)
    {

    }

}
