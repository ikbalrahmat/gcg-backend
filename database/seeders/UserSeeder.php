<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@peruri.co.id',
            'password' => Hash::make('SuperAdmin123!'),
            'role' => 'super_admin',
            'is_first_login' => false,
        ]);
    }
}
