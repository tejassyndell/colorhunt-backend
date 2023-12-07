<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportTool extends Model
{
	protected $table = 'importdata';
    protected $fillable = [
        'ArticleId', 'ArticleNumber', 'Category', 'CategoryID', 'Colorflag', 'ArticleOpenFlag', 'Brand', 'BrandID', 'StyleDescription', 'Color', 'NoPacks', 'Rate', 'SizeId', 'RatioId', 'Weight'
    ];
}
