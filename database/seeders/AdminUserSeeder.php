<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'zalhom@gmail.com');

        $admin = User::query()
            ->where('email', $email)
            ->orWhere('email', 'admin@supplychain.test')
            ->first() ?? new User();

        $admin->fill([
            'name' => 'Zalhom',
            'email' => $email,
            'password' => env('ADMIN_PASSWORD', 'zalhom123'),
            'role' => 'Admin',
            'department' => 'Risk Intelligence',
        ])->save();
    }
}
