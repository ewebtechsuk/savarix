<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateSuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@savirix.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'changeme')),
                'is_admin' => 1,
            ]
        );
    }
}
