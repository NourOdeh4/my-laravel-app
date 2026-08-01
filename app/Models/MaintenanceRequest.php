<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'technician_id',
        'service_id',
        'problem_description',
        'damaged_panels_count',
        'battery_count',
        'location',
        'priority',
        'status',
        'battery_type',
'ownership_duration',
'image',
    ];
     public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // 🔥 العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 العلاقة مع الخدمة (ألواح / بطاريات / إنفرتر)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
