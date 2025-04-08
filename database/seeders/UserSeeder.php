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
        DB::table('kemahasiswaan')->truncate();
        DB::table('konselor')->truncate();
        DB::table('mahasiswa')->truncate();
        DB::table('keasramaan')->truncate();
        DB::table('dosen')->truncate();
        DB::table('orang_tua')->truncate();
        DB::table('dosen_wali')->truncate();

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
        // Define users with roles
        $users = [
            [
                'username' => 'kemahasiswaan',
                'password' => Hash::make('admin'),
                'role' => 'kemahasiswaan',
                'anak_wali' => null, // No anak wali for admin
            ],
            [
                'username' => 'konselor',
                'password' => Hash::make('admin'),
                'role' => 'konselor',
                'anak_wali' => null, // No anak wali for admin
            ],
            // Mahasiswa users (12IF1 - 5 students)
            ['username' => 'ifs19001', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19002', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19003', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19004', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19005', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            // Mahasiswa users (12IF2 - 5 students)
            ['username' => 'ifs19035', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19036', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19037', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19038', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19039', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            // Dosen users
            ['username' => 'dosen', 'password' => Hash::make('dosen'), 'role' => 'dosen', 'anak_wali' => null],
            ['username' => '0308190348', 'password' => Hash::make('dosen'), 'role' => 'dosen', 'anak_wali' => null],
            ['username' => '0311020009', 'password' => Hash::make('dosen'), 'role' => 'dosen', 'anak_wali' => null],
            ['username' => '0309020008', 'password' => Hash::make('dosen'), 'role' => 'dosen', 'anak_wali' => null],
            // Other roles
            ['username' => 'keasramaan', 'password' => Hash::make('keasramaan'), 'role' => 'keasramaan', 'anak_wali' => null],
            ['username' => 'orangtua', 'password' => Hash::make('orangtua'), 'role' => 'orang_tua', 'anak_wali' => null],
        ];

        // Array of unique NIMs for mahasiswa
        $nims = [
            // 12IF1 (5 students)
            '11S19001', '11S19002', '11S19003', '11S19004', '11S19005',
            // 12IF2 (5 students)
            '11S19035', '11S19036', '11S19037', '11S19038', '11S19039',
        ];

        $kelas = [
            // 12IF1 (5 students)
            '12IF1', '12IF1', '12IF1', '12IF1', '12IF1',
            // 12IF2 (5 students)
            '12IF2', '12IF2', '12IF2', '12IF2', '12IF2',
        ];

        $names = [
            // 12IF1 (5 students)
            'Bungaran Martua Pakpahan', 'Hans Mayson Pranajaya Situmeang', 'Rafelli Simangunsong',
            'Sophian Kalam Nainggolan', 'Jhonatan Edward Sitorus',
            // 12IF2 (5 students)
            'Rahmad Joko Susilo Situmorang', 'Wybren Agung manik', 'Rio Efraim Simanjuntak',
            'Jogi Arif Guruh Sitinjak', 'Albert Samuel Sormin',
        ];

        // Array for dosen
        $nips = [
            'dosen', '0309130087', '0311020009', '0309020008'
        ];

        $dosenNames = [
            'Dosen', 'Arie Satia Dharma, S.T, M.Kom.',
            'Dr. Arlinta Christy Barus, ST., M.InfoTech.',
            'Dr. Johannes Harungguan Sianipar, S.T., M.T.'
        ];

        $dosenClasses = [
            '12IF2', '11IF1,11IF2,14IF2', '14IF1', '12IF1,12IF2',
        ];

        

        // Counter for assigning NIMs and NIPs
        $nipIndex = 0;
        $nimIndex = 0;

        // Insert users and populate role-specific tables
        foreach ($users as $user) {
            // Insert into the users table
            $userId = DB::table('users')->insertGetId([
                'username' => $user['username'],
                'password' => $user['password'],
                'role' => $user['role'],
                'anak_wali' => $user['anak_wali'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert into the role-specific table
            switch ($user['role']) {
                case 'kemahasiswaan':
                    DB::table('kemahasiswaan')->insert([
                        'username' => $user['username'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'konselor':
                    DB::table('konselor')->insert([
                        'username' => $user['username'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;

                case 'mahasiswa':
                    DB::table('mahasiswa')->insert([
                        'username' => $user['username'],
                        'nim' => $nims[$nimIndex],
                        'nama' => $names[$nimIndex],
                        'kelas' => $kelas[$nimIndex],
                        'ID_Dosen' => '0309020008', // Set the ID_Dosen to match the dosen user
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $nimIndex++;
                    break;

                case 'dosen':
                    DB::table('dosen')->insert([
                        'username' => $user['username'],
                        'nip' => $nips[$nipIndex],
                        'nama' => $dosenNames[$nipIndex],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('dosen_wali')->insert([
                        'username' => $user['username'],
                        'kelas' => $dosenClasses[$nipIndex],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);


                    $nipIndex++;
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
                        'nim' => '11S19001', // Fixed: Assign a default NIM for orang tua
                        'no_hp' => '+6281377385300',
                          'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
            }
        }
    }   
}