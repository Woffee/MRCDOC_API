<?php
namespace App\Http\Models;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $table = 'comments';

    public $timestamps = false;

    protected $guarded = [];

}