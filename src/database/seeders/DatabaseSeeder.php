<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // $this->call(AdminSeeder::class);
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@modalrakyat.com',
            'password' => Hash::make('admin123'),
            'role'=> 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Kopi',
            'email' => 'kopi@example.com',
            'password' => Hash::make('kopienak'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
