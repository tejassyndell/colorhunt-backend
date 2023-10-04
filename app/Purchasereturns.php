<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchasereturns extends Model
{
    protected $table = 'purchasereturn';
    protected $fillable = ['PurchaseReturnNumber','VendorId','ArticleId', 'InwardId','TotalNoPacks','RemainingNoPacks','ReturnNoPacks','UserId','Remark','ArticleRate','CreatedDate'];    
}
