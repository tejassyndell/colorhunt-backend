<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Financialyear extends Model
{
    protected $table = 'financialyear';
    protected $fillable = ['StartYear','EndYear'];
}
