<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function deleted(User $user)
    {
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
}