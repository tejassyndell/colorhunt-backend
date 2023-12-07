<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Articlelaunch extends Model
{
    protected $table = 'articlelaunch';
    protected $fillable = ['ArticleId','LaunchDate', 'CreatedDate', 'PartyId', 'UpdatedDate'];

}
