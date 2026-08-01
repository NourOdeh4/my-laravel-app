<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Device;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $solarRequest = Service::where(
            'title',
            'طلب تركيب طاقة شمسية'
        )->first();

        Device::insert([

            [
                'service_id' => $solarRequest->id,
                'title' => 'براد'
            ],

            [
                'service_id' => $solarRequest->id,
                'title' => 'غسالة'
            ],

            [
                'service_id' => $solarRequest->id,
                'title' => 'مكيف'
            ],

            [
                'service_id' => $solarRequest->id,
                'title' => 'إنارة'
            ],

            [
                'service_id' => $solarRequest->id,
                'title' => 'أدوات كهربائية بسيطة (خلاط، ليس... إلخ)'
            ],

        ]);
    }
}
