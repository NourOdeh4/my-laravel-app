<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarRequest extends Model
{
   protected $fillable = [
    'user_id',
    'solar_package_id',
    'status',
    'installation_request_id'
];

public function solarPackage()
{
    return $this->belongsTo(SolarPackage::class);
}

public function devices()
{
    return $this->hasMany(SolarRequestDevice::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
public function technician()
{
    return $this->belongsTo(User::class, 'technician_id');
}


}
