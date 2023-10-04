<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salesreturn extends Model
{
    protected $table = 'salesreturn';
    protected $fillable = ['SalesReturnNumber','OutwardId','Outletflag', 'PartyId','OutletPartyId','Remark','ArticleId','NoPacks','UserId','OutwardRate','CreatedDate'];
}
