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
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'anak_wali' => null,
            ],
            // Mahasiswa users (12IF1)
            ['username' => 'ifs19001', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19002', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19003', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19004', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19005', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19006', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19007', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19008', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19009', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19010', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19011', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19012', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19013', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19014', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19016', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19017', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19018', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19019', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19020', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19021', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19022', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19023', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19024', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19025', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19026', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19027', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19028', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19029', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19030', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19031', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19032', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19033', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19034', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            // Mahasiswa users (12IF2)
            ['username' => 'ifs19035', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19036', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19037', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19038', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19039', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19040', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19041', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19042', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19043', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19044', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19045', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19046', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19047', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19048', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19049', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19050', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19051', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19052', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19054', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19055', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19056', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19057', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19058', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19059', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19060', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19061', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19062', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19063', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19064', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19065', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19067', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
            ['username' => 'ifs19068', 'password' => Hash::make('mahasiswa'), 'role' => 'mahasiswa', 'anak_wali' => '0309020008'],
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
            // 12IF1
            '11S19001', '11S19002', '11S19003', '11S19004', '11S19005', '11S19006', '11S19007', '11S19008',
            '11S19009', '11S19010', '11S19011', '11S19012', '11S19013', '11S19014', '11S19016', '11S19017',
            '11S19018', '11S19019', '11S19020', '11S19021', '11S19022', '11S19023', '11S19024', '11S19025',
            '11S19026', '11S19027', '11S19028', '11S19029', '11S19030', '11S19031', '11S19032', '11S19033',
            '11S19034',
            // 12IF2
            '11S19035', '11S19036', '11S19037', '11S19038', '11S19039', '11S19040', '11S19041', '11S19042',
            '11S19043', '11S19044', '11S19045', '11S19046', '11S19047', '11S19048', '11S19049', '11S19050',
            '11S19051', '11S19052', '11S19054', '11S19055', '11S19056', '11S19057', '11S19058', '11S19059',
            '11S19060', '11S19061', '11S19062', '11S19063', '11S19064', '11S19065', '11S19067', '11S19068',
        ];

        $kelas = [
            // 12IF1 (34 students)
            '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1',
            '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1',
            '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1',
            '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1', '12IF1',
            '12IF1',
            // 12IF2 (33 students)
            '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2',
            '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2',
            '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2',
            '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2', '12IF2',
        ];

        $names = [
            // 12IF1
            'Bungaran Martua Pakpahan', 'Hans Mayson Pranajaya Situmeang', 'Rafelli Simangunsong',
            'Sophian Kalam Nainggolan', 'Jhonatan Edward Sitorus', 'Daniel Fernandez Lumbanraja',
            'Tesalonika Siahaan', 'Rewina Pakpahan', 'Renta Sri Hertati Sitorus', 'Kristina Tampubolon',
            'Ferdinand Partahi Jaya Tambunan', 'Willem Alexander Suranta Pinem', 'Yosia Sihaloho',
            'Jonggi Vegas Sitorus', 'Timothy Sipahutar', 'Montana Gurning',
            'Juliant Omri Christson Nathannyahu', 'Edrei Abiel Benaya Siregar', 'Talenta Maria Sihotang',
            'Trivena Yuli Necia Panjaitan', 'Gabryella Apriani Sinaga', 'Sarah Oktavia br Pasaribu',
            'Theresia Mega Tiurma Rumapea', 'PRAWITA DWI FRISKILA', 'Elisa Claudia Tinambunan',
            'Darel Deonaldo Aloysius Pinem', 'Deiva Imanuela Pasaribu', 'Alfrendo Stenley Silalahi',
            'Yuan Halasan Siagian', 'Gunado Siregar', 'Fori Okto Pakpahan', 'BINTANG LBN RAJA',
            'Hotmangasi Manurung',
            // 12IF2
            'Rahmad Joko Susilo Situmorang', 'Wybren Agung manik', 'Rio Efraim Simanjuntak',
            'Jogi Arif Guruh Sitinjak', 'Albert Samuel Sormin', 'Judah Michael Parluhutan Sitorus',
            'Rivaldo Gabriel S', 'Riski Yan Daniel Simanjuntak', 'Hari Dominggo Soarest Joab Siburian',
            'Sondang Kevin P Sihaloho', 'Josua Gaolus Nainggolan', 'Deny Ramadhan Pane',
            'Andreas Hatigoran', 'Nicholas Tio Sibarani', 'Albet Matthew Best Nainggolan',
            'Risky Junior Martua Panggabean', 'Handy Sonflow Sitepu', 'Rens junior sibarani',
            'Yoni Herlina Siahaan', 'Kartika Novia Hutauruk', 'Esi Butarbutar', 'Hana Maria Siahaan',
            'Yuliana Nainggolan', 'Puan Maharani Sirait', 'Evi Rosalina Silaban', 'Agnes Bertua Nababan',
            'GRACE STEFANI NATALIA PAKPAHAN', 'Hanna Suryani Simarmata', 'Patricia Melissa Yolanda Sibarani',
            'Aryanti Verina Putri Siregar', 'Cynthia Veronika Pardede', 'TASYA JULI CHANTIKA GURNING',
        ];

        // Array for dosen
        $nips = [
            'dosen', '0308190348', '0311020009', '0309020008'
        ];

        $dosenNames = [
            'Dosen', 'Iustisia Natalia Simbolon, S.Kom., M.T.',
            'Dr. Arlinta Christy Barus, ST., M.InfoTech.',
            'Dr. Johannes Harungguan Sianipar, S.T., M.T.'
        ];

        $dosenClasses = [
            '12IF2', '12IF2', '14IF1', '12IF1,12IF2',
        ];

        // Counter for assigning NIMs and NIPs
        $nipIndex = 0;
        $nimIndex = 0;

        // Insert users and populate role-specific tables
        foreach ($users as $user) {
            // Insert into the users table
            DB::table('users')->insert([
                'username' => $user['username'],
                'password' => $user['password'],
                'role' => $user['role'],
                'anak_wali' => $user['anak_wali'],
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
                        'nim' => $nims[$nimIndex],
                        'nama' => $names[$nimIndex],
                        'kelas' => $kelas[$nimIndex],
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
                        'nim' => '11S19001',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    break;
            }
        }
    }
}