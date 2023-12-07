<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transportoutlet extends Model
{
    protected $table = 'transportoutlet';
    protected $fillable = ['OutwardNumberId','PartyId','TransportStatus', 'Remarks', 'UserId', 'ReceivedDate'];
}
