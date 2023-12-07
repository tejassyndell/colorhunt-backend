<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutletSalesreturn extends Model
{
    protected $table = 'outletsalesreturn';
    protected $fillable = ['SalesReturnNumber','OutletId','PartyId','OutletPartyId','Remark','ArticleId','NoPacks','UserId','OutletRate','CreatedDate'];
}
