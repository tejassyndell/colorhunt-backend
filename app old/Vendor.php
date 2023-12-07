<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';
    protected $fillable = ['Name','Address','PhoneNumber','ContactPerson','GSTNumber'];
}
