<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorMaintenanceRequest extends Model
{

    protected $fillable = [

        'user_id',
        'generator_name',
        'generator_id',
        'problem_description',
        'generator_power',
        'working_hours',
        'priority',
        'status',
        'technician_id'

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
        return $this->belongsTo(User::class,'technician_id');
    }

}
