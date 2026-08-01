<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SolarPackage;

class SolarPackageSeeder extends Seeder
{
    public function run(): void
    {
        SolarPackage::create([
            'name' => 'تركيبة 1',
            'inverter_watt' => 1000,
            'battery' => 'Lithium',
            'panels' => 2,
            'price' => 700,
            'capacity_watt' => 20000,
        ]);

        SolarPackage::create([
            'name' => 'تركيبة 2',
            'inverter_watt' => 4000,
            'battery' => '5 kWh',
            'panels' => 6,
            'price' => 1500,
            'capacity_watt' => 30000,
        ]);

        SolarPackage::create([
            'name' => 'تركيبة 3',
            'inverter_watt' => 6000,
            'battery' => '10 kWh',
            'panels' => 10,
            'price' => 2500,
            'capacity_watt' => 50000,
        ]);
    }

}
