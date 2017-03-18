<?php
namespace App\Http\Models;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    protected $table = 'files';

    public $timestamps = false;

    protected $guarded = [];

}