<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@taskai.local'],
            [
                'name' => 'Task AI Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        $this->command->info('Task AI admin: admin@taskai.local / password');
    }
}
