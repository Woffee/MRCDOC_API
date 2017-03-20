<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 2017/3/18
 * Time: 14:30
 */

namespace App\Http\Services;

use App\Http\Models\Files;
use App\Http\Models\Users;

class UserService
{
    private $_file_id;
    private $_filename;
    private $_creator;
    private $_content ='';
    private $_type = 1;
    private $_in_folder = 'desk';

    public function checkName($username)
    {
        $res = Users::where('username',$username)->first();
        return $res ? true : false;
    }



}