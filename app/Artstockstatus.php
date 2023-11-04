<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artstockstatus extends Model
{
    protected $table = 'artstockstatus';

    protected $fillable = ['Id', 'outletId', 'ArticleId', 'ArticleNumber', 'ArticleOpenFlag', 'SalesNoPacks', 'TotalPieces', 'ArticleColor', 'ArticleSize', 'ArticleRatio', 'Colorflag', 'Title', 'BrandName', 'Subcategory', 'SeriesName', 'Series', 'StyleDescription', 'TotalArticleRatio', 'InwardDate', 'updated_at', 'created_at'];

}
