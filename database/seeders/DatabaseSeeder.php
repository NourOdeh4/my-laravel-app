<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void

    {
        $this->call([
    SuperAdminSeeder::class,
])
;
$this->call(GeneratorSeeder::class);
<<<<<<< HEAD
$this->call(ProductSeeder::class);
=======
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
