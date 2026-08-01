<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolarRequest extends Model
{
   protected $fillable = [
    'user_id',
    'solar_package_id',
<<<<<<< HEAD
    'status',
    'installation_request_id'
=======
    'status'
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
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
