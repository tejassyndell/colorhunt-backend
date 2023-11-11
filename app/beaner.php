<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class beaner extends Model
{
    use HasFactory;
    protected $table = 'beaner';
    protected $fillable = ['image','Id','created_at','updated_At' ];
}
