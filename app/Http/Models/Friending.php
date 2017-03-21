<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/21
 * Time: 10:26
 */

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Friending extends Eloquent
{
    protected $table = 'friending';

    public $timestamps = false;
}