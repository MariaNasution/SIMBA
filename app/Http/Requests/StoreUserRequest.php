<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Mahasiswa;
use App\Models\OrangTua;
use App\Models\Dosen;
use App\Models\Konselor;
use App\Models\Kemahasiswaan;
use App\Models\Keasramaan;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            if ($role === 'mahasiswa') {
                if (!$this->filled('nama')) {
                    $validator->errors()->add('nama', 'Nama wajib diisi untuk mahasiswa.');
                }
                if (!$this->filled('nim')) {
                    $validator->errors()->add('nim', 'NIM wajib diisi untuk mahasiswa.');
                }
                if (!$this->filled('kelas')) {
                    $validator->errors()->add('kelas', 'Kelas wajib diisi untuk mahasiswa.');
                }
                if (!$this->filled('anak_wali')) {
                    $validator->errors()->add('anak_wali', 'Dosen wali wajib dipilih untuk mahasiswa.');
                }
            }
            if ($role === 'orang_tua' && !$this->filled('no_hp')) {
                $validator->errors()->add('no_hp', 'Nomor HP wajib diisi untuk orang tua.');
            }
        });
    }

    public function rules()
    {
        $userId = $this->user ? $this->user->id : null;
        $isUpdate = $this->route()->getName() === 'admin.users.update';

        $rules = [
            'username' => 'required|string|max:255|unique:users,username,' . $userId,
            'role' => 'required|string|in:mahasiswa,konselor,kemahasiswaan,dosen,keasramaan,orang_tua',
            'nama' => 'nullable|string|max:255',
            'nim' => [
                'nullable',
                'string',
                'max:8',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($this->input('role') === 'mahasiswa') {
                        if (Mahasiswa::where('nim', $value)->where('username', '!=', $this->user?->username)->exists()) {
                            $fail('NIM sudah terdaftar untuk mahasiswa.');
                        }
                    } elseif ($this->input('role') === 'orang_tua') {
                        if ($value && !Mahasiswa::where('nim', $value)->exists()) {
                            $fail('NIM mahasiswa tidak ditemukan.');
                        }
                        if (OrangTua::where('nim', $value)->where('username', '!=', $this->user?->username)->exists()) {
                            $fail('NIM sudah terdaftar untuk orang tua.');
                        }
                    }
                },
            ],
            'nip' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($this->input('role') === 'dosen' && Dosen::where('nip', $value)->where('username', '!=', $this->user?->username)->exists()) {
                        $fail('NIP sudah terdaftar untuk dosen.');
                    } elseif ($this->input('role') === 'konselor' && Konselor::where('nip', $value)->where('username', '!=', $this->user?->username)->exists()) {
                        $fail('NIP sudah terdaftar untuk konselor.');
                    } elseif ($this->input('role') === 'kemahasiswaan' && Kemahasiswaan::where('nip', $value)->where('username', '!=', $this->user?->username)->exists()) {
                        $fail('NIP sudah terdaftar untuk kemahasiswaan.');
                    } elseif ($this->input('role') === 'keasramaan' && Keasramaan::where('nip', $value)->where('username', '!=', $this->user?->username)->exists()) {
                        $fail('NIP sudah terdaftar untuk keasramaan.');
                    }
                },
            ],
            'kelas' => 'nullable|string|max:10',
            'anak_wali' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($this->input('role') === 'mahasiswa' && $value && !Dosen::where('nip', $value)->exists()) {
                        $fail('NIP dosen wali tidak ditemukan.');
                    }
                },
            ],
            'ID_Perwalian' => 'nullable|exists:perwalian,ID_Perwalian',
            'no_hp' => 'nullable|string|max:15',
        ];

        if ($isUpdate) {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        } else {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'username.unique' => 'Username sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nim.max' => 'NIM tidak boleh lebih dari 8 karakter.',
            'nip.max' => 'NIP tidak boleh lebih dari 20 karakter.',
        ];
    }
}