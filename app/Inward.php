<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inward extends Model
{
    protected $table = 'inward';
    protected $fillable = ['ArticleId','NoPacks','InwardDate','GRN','Weight','SalesNoPacks','TotalSetQuantity','rejections'];
}