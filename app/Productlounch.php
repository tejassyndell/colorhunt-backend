<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Productlounch extends Model
{
     protected $table = 'productlounch';
    protected $fillable = ['ArticleId','ProductStatus','Remarks', 'UserId'];
}
