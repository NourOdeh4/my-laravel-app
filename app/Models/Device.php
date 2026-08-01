<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'service_id',
        'title'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
