<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin'); // Hanya admin yang bisa akses
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            if ($role === 'mahasiswa' && !$this->input('kelas')) {
                $validator->errors()->add('kelas', 'Kelas wajib diisi untuk mahasiswa.');
            }
            if ($role === 'orang_tua' && !$this->input('no_hp')) {
                $validator->errors()->add('no_hp', 'Nomor HP wajib diisi untuk orang tua.');
            }
        });
    }

    public function rules()
    {
        $userId = $this->user ? $this->user->id : null;

        return [
            'username' => 'required|string|max:255|unique:users,username,' . $userId,
            'password' => 'required|string|min:6|confirmed', // Konfirmasi password
            'role' => 'required|string|in:mahasiswa,konselor,kemahasiswaan,dosen,keasramaan,orang_tua',
            'nama' => 'required_if:role,mahasiswa,dosen|string|max:255|nullable',
            'nim' => [
                'required_if:role,mahasiswa,orang_tua',
                'string',
                'max:20',
                'unique:mahasiswa,nim,' . ($this->user ? $this->user->mahasiswa?->nim : null),
                // Validasi nim untuk orang_tua harus ada di tabel mahasiswa
                'exists:mahasiswa,nim' => $this->input('role') === 'orang_tua',
            ],
            'nip' => [
                'required_if:role,dosen,konselor,kemahasiswaan,keasramaan',
                'string',
                'max:20',
                'unique:dosen,nip,' . ($this->user ? $this->user->dosen?->nip : null),
                'unique:konselor,nip,' . ($this->user ? $this->user->konselor?->nip : null),
                'unique:kemahasiswaan,nip,' . ($this->user ? $this->user->kemahasiswaan?->nip : null),
                'unique:keasramaan,nip,' . ($this->user ? $this->user->keasramaan?->nip : null),
            ],
            'kelas' => 'required_if:role,mahasiswa|string|max:10|nullable',
            'ID_Dosen' => 'required_if:role,mahasiswa|exists:dosen,username|nullable',
            'ID_Perwalian' => 'nullable|exists:perwalian,ID_Perwalian',
            'no_hp' => 'required_if:role,orang_tua|string|max:15|nullable',
        ];
    }

    public function messages()
    {
        return [
            'username.unique' => 'Username sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'nim.exists' => 'NIM mahasiswa tidak ditemukan.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'ID_Dosen.exists' => 'Dosen wali tidak valid.',
        ];
    }
}