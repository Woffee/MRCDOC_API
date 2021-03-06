<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FileService;
use App\Http\Services\WriterService;
use App\Http\Models\Writers;

class FileController extends Controller
{
    /**
     * 文件详情
     *
     * @param $fileid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($fileid, Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        /** 检查是否是协作者 */
        $res = Writers::where('uid',$uid)
            ->where('file_id',$fileid)
            ->where('is_del',0)
            ->first();
//        if( empty($res) ){
//            return $this->error('您没有编辑或查看此文件的权限');
//        }

        $file = (new FileService())->getFileInfo($uid,$fileid);

        return $this->success(['file'=>$file]);
    }

    /**
     * 桌面文件列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function desk(Request $request)
    {
        $inputs = $request->only('uid','page','pagesize');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $page = isset($inputs['page']) ? $inputs['page'] : 1;
        $pagesize = isset($inputs['pagesize']) ? $inputs['pagesize'] : 10;

        //xhprof_enable();

        $fileService = new FileService();
        $count = $fileService->getFilesCountInFolder($uid,'desk');
        $files = $fileService->getFilesByFolderId($uid,'desk',$page,$pagesize);

        //$xhprof_data = xhprof_disable();
        //dd($xhprof_data);

        return $this->success(['count'=>$count,'files'=>$files]);
    }

    /**
     * 文件夹下的文件列表
     *
     * @param $folderid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function folder($folderid,Request $request)
    {
        $inputs = $request->only('uid','page','pagesize');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $page = isset($inputs['page']) ? $inputs['page'] : 1;
        $pagesize = isset($inputs['pagesize']) ? $inputs['pagesize'] : 10;

        $fileService = new FileService();
        $count = $fileService->getFilesCountInFolder($uid,$folderid);
        $files = $fileService->getFilesByFolderId($uid,$folderid,$page,$pagesize);

        return $this->success(['count'=>$count,'files'=>$files]);
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

        if( !$fileService->checkOwnFolder($uid,$inFolder) ){
            return $this->error('创建文件失败：当前文件夹不存在');
        }

        if( empty($file_id) ){
            return $this->error('创建文件失败');
        }

        $weriterService = new WriterService();
        $res = $weriterService->createWriter($file_id,$uid);
        if( !$res ){
            return $this->error('添加协作者失败');
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
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $file_id  = $inputs['file_id'];
        $filename = $inputs['filename'];

        $fileService = new FileService();
        $fileService->setCreator($uid);
        $fileService->setFileId($file_id);
        $fileService->setFilename($filename);

        $res = $fileService->updateFile();
        if( empty($res) ){
            return $this->error('修改文件失败');
        }
        return $this->success();
    }

    public function moveFiles(Request $request)
    {
        $inputs = $request->only('uid','file_ids','move_to');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
            'file_ids'   =>    'required',
            'move_to'    =>    'required',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $file_ids  = $inputs['file_ids'];
        $moveTo  = $inputs['move_to'];

        $fileService = new FileService();
        $fileService->setCreator($uid);

        if( !$fileService->moveFiles($uid,$file_ids,$moveTo)  ){
            return $this->error('移动失败');
        }
        return $this->success();
    }

    public function deleteFiles(Request $request)
    {
        $inputs = $request->only('uid','file_ids');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
            'file_ids'   =>    'required',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $file_ids  = $inputs['file_ids'];

        $fileService = new FileService();
        $fileService->setCreator($uid);

        if( !$fileService->deleteFiles($uid,$file_ids)  ){
            return $this->error('删除失败');
        }
        return $this->success();
    }

    public function destroyFiles(Request $request)
    {
        $inputs = $request->only('uid','file_ids');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
            'file_ids'  =>    'required',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $fileIds = !empty($inputs['file_ids']) ? $inputs['file_ids'] : '';

        $fileService = new FileService();

        if( !$fileService->destroyFile($uid,$fileIds)  ){
            return $this->error('彻底删除失败：文件不存在或已被彻底删除');
        }
        return $this->success();
    }

    public function recentFiles(Request $request)
    {
        $inputs = $request->only('uid');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer'
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];

        $fileService = new FileService();
        $files = $fileService->getRecentFiles($uid);

        return $this->success(['recent_files'=>$files]);
    }

}
