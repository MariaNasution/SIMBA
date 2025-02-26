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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate all tables to start fresh
        DB::table('users')->truncate();
        DB::table('admin')->truncate();
        DB::table('mahasiswa')->truncate();
        DB::table('keasramaan')->truncate();
        DB::table('dosen')->truncate();
        DB::table('orang_tua')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed users and populate role-specific tables
        $this->seedUsersAndRoles();
    }

    /**
     * Seed users and populate role-specific tables.
     */
    private function seedUsersAndRoles()
    {
        // Define users with roles and unique NIMs for mahasiswa
        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ],
                'anak_wali' => null, // No anak wali for admin
            ],
            
            [
                'username' => 'ifs19001', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19002', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19003', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19004', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19005', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19031', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19032', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19033', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19034', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', 
            ],
            [
                'username' => 'ifs19035', // Added second student
                'password' => Hash::make('mahasiswa'),
                'role' => 'mahasiswa',
                'anak_wali' => 'dosen', // Mahasiswa is under dosen wali 1
            ],
            [
                'username' => 'dosen',
                'password' => Hash::make('dosen'),
                'role' => 'dosen',
                'anak_wali' => null, // Dosen as wali, no anak wali
            ],
            [
                'username' => 'keasramaan',
                'password' => Hash::make('keasramaan'),
                'role' => 'keasramaan',
                'anak_wali' => null, // No anak wali for keasramaan
            ],
            [
                'username' => 'orangtua',
                'password' => Hash::make('orangtua'),
                'role' => 'orang_tua',
            ],
        ];
                'anak_wali' => null, // No anak wali for orang tua
            ],
        ];

        // Array of unique NIMs for mahasiswa (must match the number of mahasiswa users)
        $nims = ['11S19001', '11S19002', '11S19003','11S19004',
                 '11S19005', '11S19031', '11S19032', '11S19033',
                 '11S19034', '11S19035'
        ];

        // Counter for assigning NIMs
        $nimIndex = 0;
      
        // Insert users and populate role-specific tables
        foreach ($users as $user) {
            // Insert into the users table
            DB::table('users')->insert([
                'username' => $user['username'],
                'password' => $user['password'],
                'role' => $user['role'],
                'anak_wali' => $user['anak_wali'], // Add anak_wali column
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert into the role-specific table
            switch ($user['role']) {
                case 'admin':
                    DB::table('admin')->insert([
                        'username' => $user['username'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'mahasiswa':
                    DB::table('mahasiswa')->insert([
                        'username' => $user['username'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                        'nim' => $nims[$nimIndex], // Assign unique NIM
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $nimIndex++; // Increment to get the next NIM
                    break;

                case 'dosen':
                    DB::table('dosen')->insert([
                        'username' => $user['username'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'keasramaan':
                    DB::table('keasramaan')->insert([
                        'username' => $user['username'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'orang_tua':
                    DB::table('orang_tua')->insert([
                        'username' => $user['username'],
                        'nim' => '11S19001',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
            }
        }
    }
}