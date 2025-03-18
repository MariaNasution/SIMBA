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

        // Truncate the perwalian and absensi tables to start fresh
        DB::table('perwalian')->truncate();
        DB::table('absensi')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed Perwalian and Absensi data
        $this->seedPerwalianAndAbsensi();
    }

    /**
     * Seed Perwalian and Absensi tables.
     */
    private function seedPerwalianAndAbsensi()
    {
        // Fetch all mahasiswa records
        $mahasiswa = DB::table('mahasiswa')->get();

        // Group mahasiswa by kelas
        $kelasGroups = $mahasiswa->groupBy('kelas');

        // Fetch the specific dosen by nip
        $dosen = DB::table('dosen')->where('nip', '0308190348')->first();
        if (!$dosen) {
            throw new \Exception("Dosen with nip 0308190348 not found.");
        }
        $idDosenWali = $dosen->nip; // Use the specific dosen's nip

        // Define possible statuses for Perwalian and statusKehadiran for Mahasiswa
        $perwalianStatuses = ['Scheduled', 'Completed', 'Canceled'];
        $kehadiranStatuses = ['Hadir', 'Tidak Hadir', 'Izin'];

        // Counter for assigning Perwalian and Absensi IDs
        $perwalianCounter = 1;

        // Create one Perwalian and Absensi record per kelas
        foreach ($kelasGroups as $kelas => $students) {
            // Create a Perwalian record for this kelas
            $perwalianId = $perwalianCounter;
            DB::table('perwalian')->insert([
                'ID_Perwalian' => $perwalianId,
                'ID_Dosen_Wali' => $idDosenWali, // Use the specific dosen's nip
                'Status' => $perwalianStatuses[array_rand($perwalianStatuses)], // Random status
                'Tanggal' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'), // Random date within the last 30 days
                'nama' => "Perwalian for $kelas", // Use a descriptive name
                'kelas' => $kelas, // Use the kelas
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create an Absensi record (no ID_Perwalian reference)
            $idAbsensi = $perwalianCounter; // Use integer for ID_Absensi
            DB::table('absensi')->insert([
                'ID_Absensi' => $idAbsensi,
                'Kelas' => $kelas, // Store the kelas in Absensi
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update all mahasiswa in this kelas to link to the Perwalian and Absensi
            DB::table('mahasiswa')
                ->where('kelas', $kelas)
                ->update([
                    'ID_Perwalian' => $perwalianId,
                    'ID_Absensi' => $idAbsensi,
                    'statusKehadiran' => $kehadiranStatuses[array_rand($kehadiranStatuses)], // Random statusKehadiran
                ]);

            $perwalianCounter++;
        }
    }
}