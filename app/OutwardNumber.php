<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutwardNumber extends Model
{
    protected $table = 'outwardnumber';
    protected $fillable = ['SoId','FinancialYearId','OutwardNumber','OutwardDate','GSTAmount','GSTPercentage','GSTType','Discount','UserId','DeleteOutward','Remarks','created_at','updated_at'];
}
