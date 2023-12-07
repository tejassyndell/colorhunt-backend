<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $table = 'outlet';
    protected $fillable = ['OutletNumberId','ArticleId','NoPacks','ArticleRate'];
}
