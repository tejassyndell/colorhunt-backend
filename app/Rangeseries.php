<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rangeseries extends Model
{
    protected $table = 'rangeseries';
    protected $fillable = ['CategoryId','SubCategoryId','SeriesName','Series'];
}
