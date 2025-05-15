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
        DB::table('admin')->truncate();
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
            [
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'anak_wali' => null,
            ],
            // Mahasiswa users (12IF1 - 5 students, assigned to NIP 0309020008)
            ['username' => 'ifs19001', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19002', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19003', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19004', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19005', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            // Mahasiswa users (12IF2 - 5 students, assigned to NIP 0309020008)
            ['username' => 'ifs19035', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19036', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19037', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19038', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19039', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            // Mahasiswa users (11IF1 - 5 students, assigned to NIP 0309130087)
            ['username' => 'ifs20001', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20002', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20003', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20004', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20005', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            // Mahasiswa users (11IF2 - 5 students, assigned to NIP 0309130087)
            ['username' => 'ifs20027', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20028', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20029', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20030', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs20032', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            // Mahasiswa users (14IF2 - 5 students, assigned to NIP 0309130087)
            ['username' => 'ifs15039', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs15045', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs16036', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs16062', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            ['username' => 'ifs17034', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309130087'],
            // Dosen users
            ['username' => '0309130087', 'password' => Hash::make('dosen'), 'role' => 'dosen', 'anak_wali' => null],
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
            // 11IF1 (5 students)
            '11S20001', '11S20002', '11S20003', '11S20004', '11S20005',
            // 11IF2 (5 students)
            '11S20027', '11S20028', '11S20029', '11S20030', '11S20032',
            // 14IF2 (5 students)
            '11S15039', '11S15045', '11S16036', '11S16062', '11S17034',
        ];

        $kelas = [
            // 12IF1 (5 students)
            '12IF1', '12IF1', '12IF1', '12IF1', '12IF1',
            // 12IF2 (5 students)
            '12IF2', '12IF2', '12IF2', '12IF2', '12IF2',
            // 11IF1 (5 students)
            '11IF1', '11IF1', '11IF1', '11IF1', '11IF1',
            // 11IF2 (5 students)
            '11IF2', '11IF2', '11IF2', '11IF2', '11IF2',
            // 14IF2 (5 students)
            '14IF2', '14IF2', '14IF2', '14IF2', '14IF2',
        ];

        $names = [
            // 12IF1 (5 students)
            'Bungaran Martua Pakpahan', 'Hans Mayson Pranajaya Situmeang', 'Rafelli Simangunsong',
            'Sophian Kalam Nainggolan', 'Jhonatan Edward Sitorus',
            // 12IF2 (5 students)
            'Rahmad Joko Susilo Situmorang', 'Wybren Agung manik', 'Rio Efraim Simanjuntak',
            'Jogi Arif Guruh Sitinjak', 'Albert Samuel Sormin',
            // 11IF1 (5 students)
            'Samuel Adika Lumban Tobing', 'Yoel Ganda Aprilco Napitupulu', 'Reinhard Hottua S',
            'Samuel Immanuel Herlinton Sibuea', 'Lasria Sri Rezeki Rajagukguk',
            // 11IF2 (5 students)
            'Bryand Christofer Sinaga', 'Lamboy Albertson Sirait', 'Rizal Sahala Bakti',
            'Vistar Tiop Raja Gukguk', 'Yosua Putra Wisesa Haloho',
            // 14IF2 (5 students)
            'Hizkia Ricky F Parhusip', 'Reikard Martua Napitupulu', 'Tangido Halomoan Sinaga',
            'Yosua Giat Raja Saragih', 'Monica Dewi Sartika Marpaung',
        ];

        // Array for dosen
        $nips = [
            '0309130087', '0311020009', '0309020008'
        ];

        $dosenNames = [
            'Arie Satia Dharma, S.T, M.Kom.',
            'Dr. Arlinta Christy Barus, ST., M.InfoTech.',
            'Dr. Johannes Harungguan Sianipar, S.T., M.T.'
        ];

        $angkatan = [
            '2020,2020,2017', // For 0309130087 (11IF1, 11IF2, 14IF2)
            '2017',           // For 0311020009 (14IF1)
            '2019,2019',      // For 0309020008 (12IF1, 12IF2)
        ];

        $dosenClasses = [
            '11IF1,11IF2,14IF2', // For 0309130087
            '14IF1',             // For 0311020009
            '12IF1,12IF2',       // For 0309020008
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
                        'nim' => $nims[$nimIndex],
                        'nama' => $names[$nimIndex],
                        'kelas' => $kelas[$nimIndex],
                        'ID_Dosen' => $user['anak_wali'], // Use the anak_wali to set the ID_Dosen
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
                        'angkatan' => $angkatan[$nipIndex],
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
                        'nim' => '11S20001', // Matches one of the new students
                        'no_hp' => '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
            }
        }
    }   
}