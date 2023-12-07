<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InwardCancellationLog extends Model
{
    protected $table = 'inwardcancellationlogs';
    protected $fillable = ['InwardId','ArticleId','NoPacks','InwardDate','GRN','Weight','created_at','updated_at'];
}
