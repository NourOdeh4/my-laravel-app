<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneratorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('generators')->insert([
            [
                'name' => 'بيركنز',
                'capacity_watt' => 3000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'فولفو',
                'capacity_watt' => 4000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'كمنز',
                'capacity_watt' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

}
