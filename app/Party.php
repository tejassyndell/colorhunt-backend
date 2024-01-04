<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $table = 'party';
    protected $fillable = ['UserId','Name','Address','PhoneNumber', 'Additional_phone_numbers','PanNumber','ContactPerson','State','City','PinCode','Country','GSTNumber','GSTType','Discount','OutletAssign','OutletArticleRate','Source'];
}
