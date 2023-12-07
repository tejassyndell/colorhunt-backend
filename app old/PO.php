<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PO extends Model
{
    protected $table = 'po';
    protected $fillable = ['PO_Number','VendorId','CategoryId','SubCategoryId','ArticleId','BrandId','NumPacks','PoDate','Remarks','PO_Image'];
}
