<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Productlaunch extends Model
{
    //
	protected $table = 'productlaunch';
    protected $fillable = ['ArticleId','ProductStatus','Remarks', 'UserId'];
}
