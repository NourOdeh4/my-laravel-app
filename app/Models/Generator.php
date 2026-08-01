<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generator extends Model
{
    protected $fillable = [
        'name',
        'capacity_watt'
    ];
}
