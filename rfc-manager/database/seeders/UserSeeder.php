<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'preferred_notification_method' => 'email',
            'email_verified_at' => now(),
            'slack_user_id' => null,
            'phone_number' => null,
        ])->assignRole('admin');
    }
}
