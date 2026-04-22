<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Jose Sierra',
            'email' => 'jose@ingeniotech.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Santiago',
            'email' => 'santiago@ingeniotech.com',
            'password' => 'password',
            'role' => 'technician',
        ]);
    }
}
