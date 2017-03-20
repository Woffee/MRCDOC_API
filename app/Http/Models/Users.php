<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/20
 * Time: 15:35
 */

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Users extends Eloquent
{
    protected $connection = 'mysql';

    protected $table = 'users';

    public $timestamps = false;
}