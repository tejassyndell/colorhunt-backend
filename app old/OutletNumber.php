<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutletNumber extends Model
{
    protected $table = 'outletnumber';
    protected $fillable = ['OutwardnumberId', 'FinancialYearId','OutletNumber','PartyId','OutletDate','UserId','OutletPartyId','OrderView','GSTAmount','GSTPercentage','Discount','Address','Contact','CreatedDate'];
}
