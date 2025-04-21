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
            ]),

            'konselor' => Konselor::create([
                'username' => $user->username,
            ]),

            'mahasiswa' => Mahasiswa::create([
                'username' => $user->username,
                'nama' => $data['nama'],
                'nim' => $data['nim'],
                'kelas' => $data['kelas'],
                'ID_Dosen' => $data['anak_wali'],
            ]),
            
            'dosen' => Dosen::create([
                'username' => $user->username,
                'nama' => $data['nama'],
                'nip' => $data['nip'],
            ]),

            'keasramaan' => Keasramaan::create([
                'username' => $user->username,
            ]),

            'orang_tua' => OrangTua::create([
                'username' => $user->username,
                'nip' => $data['nip'],
                'no_hp' => $data['no_hp'],
            ]),
            default => null
        };

        return $user;
    }

    public function updateUser(User $user, array $data)
    {
        $user->update([
            'username' => $data['username'],
            'role' => $data['role'],
        ]);

        if ($user->role === 'mahasiswa') {
            $user->mahasiswa()->update([
                'nama' => $data['nama'],
                'nim' => $data['nim'],
                'kelas' => $data['kelas'],
                'ID_Dosen' => $data['anak_wali'],
            ]);
        }
    }
}
