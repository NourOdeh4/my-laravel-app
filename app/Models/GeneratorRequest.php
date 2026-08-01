<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorRequest extends Model
{
    protected $fillable = [
        'user_id',
        'installation_request_id',
        'generator_id',
        'technician_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generator()
    {
        return $this->belongsTo(Generator::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
