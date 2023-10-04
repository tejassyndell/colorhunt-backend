<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stocktransfer extends Model
{
    protected $table = 'stocktransfer';
    protected $fillable = ['StocktransferNumberId','ConsumedArticleId','ConsumedNoPacks','TotalConsumedNoPacks','TransferArticleId','TransferNoPacks','TotalTransferNoPacks'];
}
