<?php

namespace App\Services;

use App\Models\Keasramaan;
use App\Models\OrangTua;
use App\Models\User;
use App\Models\Kemahasiswaan;
use App\Models\Konselor;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function createUserWithRole(array $data): User
    {
        Log::info('Creating user with role', ['data' => $data]);

        // Cari username dosen berdasarkan anak_wali (NIP) jika role adalah mahasiswa
        $dosenUsername = null;
        if ($data['role'] === 'mahasiswa' && isset($data['anak_wali'])) {
            $dosen = Dosen::where('nip', $data['anak_wali'])->first();
            $dosenUsername = $dosen ? $dosen->username : null;
        }

        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_approved' => false,
            'anak_wali' => $data['anak_wali'] ?? null,
        ]);

        match ($data['role']) {
            'kemahasiswaan' => Kemahasiswaan::create([
                'username' => $user->username,
                'nip' => $data['nip'] ?? null,
            ]),
            'konselor' => Konselor::create([
                'username' => $user->username,
                'nip' => $data['nip'] ?? null,
            ]),
            'mahasiswa' => Mahasiswa::create([
                'nim' => $data['nim'] ?? null,
                'username' => $user->username,
                'ID_Dosen' => $dosenUsername, // Simpan username dosen sebagai ID_Dosen
                'ID_Perwalian' => $data['ID_Perwalian'] ?? null,
                'nama' => $data['nama'] ?? null,
                'kelas' => $data['kelas'] ?? null,
            ]),
            'dosen' => Dosen::create([
                'username' => $user->username,
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama'] ?? null,
            ]),
            'keasramaan' => Keasramaan::create([
                'username' => $user->username,
                'nip' => $data['nip'] ?? null,
            ]),
            'orang_tua' => OrangTua::create([
                'username' => $user->username,
                'nim' => $data['nim'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
            ]),
            default => null
        };

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        Log::info('Updating user', ['user_id' => $user->id, 'data' => $data]);

        // Cari username dosen berdasarkan anak_wali (NIP) jika role adalah mahasiswa
        $dosenUsername = null;
        if ($data['role'] === 'mahasiswa' && isset($data['anak_wali'])) {
            $dosen = Dosen::where('nip', $data['anak_wali'])->first();
            $dosenUsername = $dosen ? $dosen->username : null;
        }

        // Hapus data relasi lama jika role berubah
        if ($user->role !== $data['role']) {
            match ($user->role) {
                'mahasiswa' => $user->mahasiswa()->delete(),
                'konselor' => $user->konselor()->delete(),
                'kemahasiswaan' => $user->kemahasiswaan()->delete(),
                'dosen' => $user->dosen()->delete(),
                'keasramaan' => $user->keasramaan()->delete(),
                'orang_tua' => $user->orangTua()->delete(),
                default => null
            };
        }

        // Update user data
        $updateData = [
            'username' => $data['username'],
            'role' => $data['role'],
            'anak_wali' => $data['anak_wali'] ?? null,
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        $user->update($updateData);

        // Update data terkait berdasarkan role
        match ($data['role']) {
            'mahasiswa' => $user->mahasiswa()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nim' => $data['nim'] ?? null,
                    'nama' => $data['nama'] ?? null,
                    'kelas' => $data['kelas'] ?? null,
                    'ID_Dosen' => $dosenUsername,
                    'ID_Perwalian' => $data['ID_Perwalian'] ?? null,
                ]
            ),
            'dosen' => $user->dosen()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nip' => $data['nip'] ?? null,
                    'nama' => $data['nama'] ?? null,
                ]
            ),
            'konselor' => $user->konselor()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nip' => $data['nip'] ?? null,
                ]
            ),
            'kemahasiswaan' => $user->kemahasiswaan()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nip' => $data['nip'] ?? null,
                ]
            ),
            'keasramaan' => $user->keasramaan()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nip' => $data['nip'] ?? null,
                ]
            ),
            'orang_tua' => $user->orangTua()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nim' => $data['nim'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                ]
            ),
            default => null
        };

        return $user;
    }
}