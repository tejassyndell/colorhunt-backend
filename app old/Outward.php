<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outward extends Model
{
    protected $table = 'outward';
    protected $fillable = ['OutwardNumberId', 'SoId', 'ArticleId', 'NoPacks','OutwardBox','OutwardRate','OutwardWeight','PartyId','PartyDiscount','ArticleOpenFlag'];
}
