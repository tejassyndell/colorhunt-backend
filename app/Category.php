<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $fillable = ['Title','Colorflag','ArticleOpenFlag','Image','Status','Mobileapp_status','ArticleAutoGenerate','ArticleSeriesAuto' ];
}
