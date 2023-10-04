<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLogs extends Model
{
    protected $table = 'userlogs';
    protected $fillable = ['Module','ModuleNumberId' , 'LogType','LogDescription','UserId','created_at', 'updated_at'];
}
