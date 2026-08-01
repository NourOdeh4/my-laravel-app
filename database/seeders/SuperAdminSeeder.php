<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@solar.com',
            ],
            [
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
