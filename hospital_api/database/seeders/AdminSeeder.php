<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Abdul Karim',
            'email' => 'karim.admin@hospital.com',
            'password' => Hash::make('123456'),
        ]);
    }
}
