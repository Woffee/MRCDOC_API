<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\FileService;
use App\Http\Services\StarService;

class StarController extends Controller
{


    /**
     * 收藏文件列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function starFilesList(Request $request)
    {
        $inputs = $request->only('uid','page','pagesize');
        $validator = app('validator')->make($inputs,[
            'uid'       =>    'required|integer',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $page = isset($inputs['page']) ? $inputs['page'] : 1;
        $pagesize = isset($inputs['pagesize']) ? $inputs['pagesize'] : 10;

        $starService = new StarService();
        $starFiles = $starService->getStarFiles($uid,$page,$pagesize);

        return $this->success( $starFiles );
    }


    public function starFile(Request $request)
    {
        $inputs = $request->only('uid','file_id','type');
        $validator = app('validator')->make($inputs,[
            'uid'        =>    'required|integer',
            'file_id'    =>    'required',
            'type'       =>    'required|integer|min:0|max:1',
        ],['required' => ':attribute不能为空']);
        if ($validator->fails()) return $this->error($validator->errors()->all());

        $uid = (int)$inputs['uid'];
        $fileId = $inputs['file_id'];
        $type = (int)$inputs['type'];

        $starService = new StarService();
        if( $type == 1 ){
            $res = $starService->starFile($uid,$fileId);
        }else{
            $res = $starService->unStarFile($uid,$fileId);
        }

        if( !$res ){
            return $this->error('操作失败');
        }
        return $this->success();
    }

}
