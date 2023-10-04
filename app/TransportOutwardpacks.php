<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransportOutwardpacks extends Model
{
    protected $table = 'transportoutwardpacks';
    protected $fillable = ['ArticleId','ColorId','OutwardId', 'NoPacks', 'PartyId', 'CreatedDate','UpdatedDate'];
}
