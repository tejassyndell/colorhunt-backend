<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SO extends Model
{
    protected $table = 'so';
    protected $fillable = ['SoNumberId','ArticleId','NoPacks', 'OutwardNoPacks','Status',"ArticleRate"];
}
