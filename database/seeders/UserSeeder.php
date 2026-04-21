<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'middle_name' => '',
                'email' => 'admin@smartgate.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'office'],
            [
                'name' => 'Registrar Office',
                'first_name' => 'Registrar',
                'last_name' => 'Office',
                'middle_name' => '',
                'email' => 'office@smartgate.com',
                'password' => Hash::make('password'),
                'role' => 'office',
            ]
        );

        User::updateOrCreate(
            ['username' => 'guard'],
            [
                'name' => 'Security Guard',
                'first_name' => 'Security',
                'last_name' => 'Guard',
                'middle_name' => '',
                'email' => 'guard@smartgate.com',
                'password' => Hash::make('password'),
                'role' => 'guard',
            ]
        );
    }
}
