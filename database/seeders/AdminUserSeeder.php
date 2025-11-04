<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'savirix@aktonz.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make(env('ADMIN_USER_PASSWORD', 'changeme')),
            ]
        );
    }
}
