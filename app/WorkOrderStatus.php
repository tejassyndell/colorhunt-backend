<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkOrderStatus extends Model
{
    protected $table = 'workorderstatus';
    protected $fillable = ['Name'];
}
