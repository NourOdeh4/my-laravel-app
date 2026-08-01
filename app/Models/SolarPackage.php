<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarPackage extends Model
{
    use HasFactory;

    protected $table = 'solar_packages';

    protected $fillable = [
        'name',
        'inverter_watt',
        'battery',
        'panels',
        'price',
        'capacity_watt',
    ];
}
