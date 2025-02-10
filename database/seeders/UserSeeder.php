<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        // Akun admin
        DB::table('users')->insert([
            'username' => 'admin',
            'password' => Hash::make('admin'), // Password untuk admin
            'nim' => 'admin',
            'role' => 'admin', // Role admin
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
