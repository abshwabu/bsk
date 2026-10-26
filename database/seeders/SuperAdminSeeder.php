<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('bina.admin_email');
        $password = config('bina.admin_password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD in your environment before seeding.');
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Administrator', 'password' => $password],
        );
    }
}
