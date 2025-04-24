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

class UserService
{
    public function createUserWithRole(array $data): User
    {
        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
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
                'nim' => $data['nim'],
                'username' => $user->username,
                'ID_Dosen' => $data['ID_Dosen'] ?? null,
                'ID_Perwalian' => $data['ID_Perwalian'] ?? null,
                'nama' => $data['nama'],
                'kelas' => $data['kelas'],
            ]),
            'dosen' => Dosen::create([
                'username' => $user->username,
                'nip' => $data['nip'],
                'nama' => $data['nama'],
            ]),
            'keasramaan' => Keasramaan::create([
                'username' => $user->username,
                'nip' => $data['nip'] ?? null,
            ]),
            'orang_tua' => OrangTua::create([
                'username' => $user->username,
                'nim' => $data['nim'],
                'no_hp' => $data['no_hp'],
            ]),
            default => null
        };

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        // Update user data
        $updateData = [
            'username' => $data['username'],
            'role' => $data['role'],
        ];

        // Update password jika diisi
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // Update data terkait berdasarkan role
        match ($user->role) {
            'mahasiswa' => $user->mahasiswa()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nim' => $data['nim'],
                    'nama' => $data['nama'],
                    'kelas' => $data['kelas'],
                    'ID_Dosen' => $data['ID_Dosen'] ?? null,
                    'ID_Perwalian' => $data['ID_Perwalian'] ?? null,
                ]
            ),
            'dosen' => $user->dosen()->updateOrCreate(
                ['username' => $user->username],
                [
                    'nip' => $data['nip'],
                    'nama' => $data['nama'],
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
                    'nim' => $data['nim'],
                    'no_hp' => $data['no_hp'],
                ]
            ),
            default => null
        };

        return $user;
    }
}