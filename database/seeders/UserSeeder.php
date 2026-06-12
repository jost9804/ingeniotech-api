<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate => idempotente: seguro de correr en cada deploy sin duplicar.
        User::firstOrCreate(
            ['email' => 'jose@ingeniotech.com'],
            [
                'name' => 'Jose Sierra',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'santiago@ingeniotech.com'],
            [
                'name' => 'Santiago',
                'password' => Hash::make('password'),
                'role' => 'technician',
            ]
        );
    }
}
