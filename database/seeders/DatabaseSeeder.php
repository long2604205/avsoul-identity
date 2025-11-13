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
        // User::factory(10)->create();
        User::create([
            'person_id' => 1, // tùy chỉnh theo ERD của bạn
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123', // sẽ tự hash nhờ 'password' => 'hashed' trong model
            'email_verified_at' => now(),
            'status' => 1,
            'version' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ]);
    }
}
