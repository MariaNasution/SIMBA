<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerwalianAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the perwalian, absensi, and notifikasi tables to start fresh
        DB::table('perwalian')->truncate();
        DB::table('absensi')->truncate();
        DB::table('notifikasi')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed Perwalian, Absensi, and Notifikasi data
        $this->seedPerwalianAndAbsensi();
    }

    /**
     * Seed Perwalian, Absensi, and Notifikasi tables.
     */
    private function seedPerwalianAndAbsensi()
    {
        // Fetch all mahasiswa records
        $mahasiswas = DB::table('mahasiswa')->get();
        // Group mahasiswa by kelas
        $kelasGroups = $mahasiswas->groupBy('kelas');

        // Fetch the specific dosen by nip
        $dosen = DB::table('dosen')->where('nip', '0308190348')->first();
        if (!$dosen) {
            throw new \Exception("Dosen with nip 0308190348 not found.");
        }
        $idDosenWali = $dosen->nip; // Use the specific dosen's nip
        // Define possible statuses for Perwalian and statusKehadiran for Mahasiswa
        $perwalianStatuses = ['Scheduled', 'Completed', 'Canceled'];
        $kehadiranStatuses = ['Hadir', 'Tidak Hadir', 'Izin'];
        $notificationMessages = [
            'Reminder: Your perwalian session is scheduled.',
            'Update: Perwalian status has changed.',
            'Notification: Please attend your perwalian.',
        ];
        
        $index = 0;
        // Create one Perwalian, Absensi, and related Notifikasi records per kelas
        foreach ($kelasGroups as $kelas => $students) {

            // Create a Perwalian record for this kelas
            DB::table('perwalian')->insert([
                'ID_Dosen_Wali' => $idDosenWali, // Use the specific dosen's nip
                'Status' => $perwalianStatuses[array_rand($perwalianStatuses)], // Random status
                'Tanggal' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'), // Random date within the last 30 days
                'nama' => "Perwalian for $kelas", // Use a descriptive name
                'kelas' => $kelas, // Use the kelas
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Create an Absensi record
            DB::table('absensi')->insert([
                'kelas' => $kelas, // Store the kelas in Absensi
                'status_kehadiran' => $students[$index]->statusKehadiran, // Use -> instead of []
                'nim' => $students[$index]->nim, // Use -> instead of []
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get the last inserted Perwalian and Absensi IDs
            $perwalianId = DB::getPdo()->lastInsertId();
            $absensiId = DB::getPdo()->lastInsertId();

            // Update all mahasiswa in this kelas to link to the Perwalian and Absensi
            DB::table('mahasiswa')
                ->where('kelas', $kelas)
                ->update([
                    'ID_Perwalian' => $perwalianId,
                    'ID_Absensi' => $absensiId,
                    'statusKehadiran' => $kehadiranStatuses[array_rand($kehadiranStatuses)], // Random statusKehadiran
                ]);
                $index++;
            // Create a notification for each mahasiswa in this kelas
            foreach ($mahasiswas as $mahasiswa) {
                DB::table('notifikasi')->insert([
                    'Pesan' => $notificationMessages[array_rand($notificationMessages)], // Random message
                    'nim' => $mahasiswa->nim,
                    'Id_Perwalian' => $perwalianId,
                    'nama' => $mahasiswa->nama, // Use the student's nama
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}