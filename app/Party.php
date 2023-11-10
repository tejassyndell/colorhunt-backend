<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $table = 'party';
    protected $fillable = ['UserId','Name','Address','PhoneNumber', 'Additional_phone_numbers','ContactPerson','State','City','PinCode','Country','GSTNumber','PanNumber','GSTType','Discount','OutletAssign','OutletArticleRate','Source'];
}
