<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outletimport extends Model
{
    protected $table = 'outletimport';
    protected $fillable = ['ArticleId','ArticleColor','ArticleSize','ArticleRatio','PartyId'];
}
