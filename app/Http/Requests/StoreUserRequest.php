<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // atau pakai policy
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            if ($role === 'mahasiswa' && !$this->input('kelas')) {
                $validator->errors()->add('kelas', 'Kelas wajib diisi untuk mahasiswa');
            }
        });
    }

    public function rules()
    {
        return [
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:mahasiswa,konselor,kemahasiswaan,dosen,keasramaan',
            'nama' => 'required_if:role,mahasiswa',
            'nim' => 'required_if:role,mahasiswa',
            'nip' => 'required_if:role,dosen',
        ];
    }
}
