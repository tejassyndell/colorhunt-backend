<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticlePhotos extends Model
{
    protected $table = 'articlephotos';
    protected $fillable = ['ArticlesId','Name','CreatedDate'];
}
