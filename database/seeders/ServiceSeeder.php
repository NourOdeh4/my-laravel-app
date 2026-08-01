<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    $solar = Service::create([
        'title' => 'خدمات الطاقة الشمسية'
    ]);

    $industrial = Service::create([
        'title' => 'خدمات الكهرباء الصناعية'
    ]);

    Service::create([
        'title' => 'متجر إلكتروني'
    ]);

    $install = Service::create([
        'title' => 'طلب تركيب طاقة شمسية',
        'parent_id' => $solar->id
    ]);

    // 🔥 هذا هو المهم
    $maintenance = Service::create([
        'title' => 'طلب صيانة',
        'parent_id' => $solar->id
    ]);

    // 🌞 أبناء طلب الصيانة
    Service::create([
        'title' => 'ألواح طاقة شمسية',
        'parent_id' => $maintenance->id
    ]);

    Service::create([
        'title' => 'الانفيرتر',
        'parent_id' => $maintenance->id
    ]);

    Service::create([
        'title' => 'البطاريات',
        'parent_id' => $maintenance->id
    ]);
    Service::firstOrCreate([
        'title' => 'تركيب مولدات صناعية',
         'parent_id' => $industrial->id
          ]);
           Service::firstOrCreate([
            'title' => 'صيانة مولدات صناعية',
            'parent_id' => $industrial->id
             ]);
}}
