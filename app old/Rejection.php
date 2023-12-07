<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rejection extends Model
{
    protected $table = 'rejections';
    protected $fillable = ['RejectionType','created_at','updated_at'];
}
