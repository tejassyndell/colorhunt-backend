<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'userrole';
    protected $fillable = ['Role','RoleType','IsActive'];
}
