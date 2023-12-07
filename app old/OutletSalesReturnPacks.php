<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutletSalesReturnPacks extends Model
{
    protected $table = 'outletsalesreturnpacks';
    protected $fillable = ['SalesReturnId','ArticleId','ColorId','OutletId','NoPacks','PartyId','CreatedDate','UpdatedDate'];
}
