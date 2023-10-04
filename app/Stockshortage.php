<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stockshortage extends Model
{
    protected $table = 'stockshortage';
    protected $fillable = ['StocktransferNumberId','ArticleId','NoPacks', 'TotalNoPacks'];
}
