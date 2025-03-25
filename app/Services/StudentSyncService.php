<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Mahasiswa;

class StudentSyncService
{
    public function syncStudents($dosenId, $ta, $semTa)
    {
        $apiToken = env('API_TOKEN');
        if (!$apiToken) {
            Log::error('API token not found in environment variables.');
            return false;
        }

        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get("https://cis-dev.del.ac.id/api/library-api/get-all-students-by-dosen-wali", [
                    'dosen_id' => $dosenId,
                    'ta' => $ta,
                    'sem_ta' => $semTa,
                ]);

            if (!$response->successful()) {
                Log::error('API request failed:', ['status' => $response->status(), 'body' => $response->body()]);
                return false;
            }

            $data = $response->json();
            $classes = $data['daftar_kelas'] ?? [];

            foreach ($classes as $class) {
                $kelas = $class['kelas'];
                $students = $class['anak_wali'] ?? [];

                foreach ($students as $student) {
                    if (empty($student['nim'])) {
                        Log::warning('Student NIM is empty:', ['student' => $student]);
                        continue;
                    }

                    $nim = $student['nim'];
                    $username = 'ifs' . substr($nim, 3);

                    $user = User::firstOrCreate(
                        ['username' => $username],
                        [
                            'password' => Hash::make('mahasiswa'),
                            'role' => 'mahasiswa',
                            'anak_wali' => $dosenId,
                        ]
                    );

                    Mahasiswa::firstOrCreate(
                        ['nim' => $nim],
                        [
                            'username' => $username,
                            'nama' => $student['nama'],
                            'kelas' => $kelas,
                            'ID_Dosen' => $dosenId,
                        ]
                    );

                    Log::info('Synced student:', ['nim' => $nim, 'username' => $username]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Exception occurred while syncing students:', ['message' => $e->getMessage()]);
            return false;
        }
    }
}