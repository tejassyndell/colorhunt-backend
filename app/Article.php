<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'article';
    protected $fillable = ['ArticleNumber','ArticleRate','ArticleColor', 'ArticleSize','ArticleRatio','ArticleOpenFlag','StyleDescription', 'ArticleStatus','CategoryId','SubCategoryId','SeriesId','BrandId','Orderset','OpeningStock','CreatedDate', 'UpdatedDate'];
}
