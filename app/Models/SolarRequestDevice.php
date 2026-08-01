<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarRequestDevice extends Model
{
   protected $fillable = [
    'solar_request_id',
    'device_user_id',
    'working_hours',
];

public function device()
{
    return $this->belongsTo(Device::class);
}
}

