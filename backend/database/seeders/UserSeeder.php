<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users using the User model
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@breitling.com',
                'password' => Hash::make('password'),
                'rank_id' => 6,
                'registration_date' => now()->subDays(30)->format('Y-m-d')
            ],
            [
                'name' => 'Lucas Moreau',
                'email' => 'lucas@example.com',
                'password' => Hash::make('password'),
                'rank_id' => 2,
                'registration_date' => now()->subDays(8)->format('Y-m-d')
            ],
            [
                'name' => 'Emma Leroy',
                'email' => 'emma@example.com',
                'password' => Hash::make('password'),
                'rank_id' => 1,
                'registration_date' => now()->subDays(5)->format('Y-m-d')
            ],
            [
                'name' => 'Thomas Petit',
                'email' => 'thomas@example.com',
                'password' => Hash::make('password'),
                'rank_id' => 3,
                'registration_date' => now()->subDays(3)->format('Y-m-d')
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie@example.com',
                'password' => Hash::make('password'),
                'rank_id' => 4,
                'registration_date' => now()->subDays(10)->format('Y-m-d')
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre@example.com',
                'password' => Hash::make('password'),
                'rank_id' => 5,
                'registration_date' => now()->subDays(15)->format('Y-m-d')
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Using factory if you have one defined
        // User::factory(10)->create();
    }
}
