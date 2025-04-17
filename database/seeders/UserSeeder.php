<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 admin users
        $admins = [
            [
                'name' => 'Admin One',
                'email' => 'admin@gmail.com',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Admin Two',
                'email' => 'admin2@gmail.com',
                'phone' => '081234567891',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Admin Three',
                'email' => 'admin3@gmail.com',
                'phone' => '081234567892',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($admins as $admin) {
            $user = User::create($admin);
            $user->assignRole('Admin');
        }

        // Create 100 regular users
        for ($i = 1; $i <= 100; $i++) {
            $user = User::create([
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@gmail.com',
                'phone' => '08' . str_pad($i, 10, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('User');
        }
    }
}
