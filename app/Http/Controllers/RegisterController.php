<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Konselor;
use App\Models\Kemahasiswaan;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Keasramaan;
use App\Models\OrangTua;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function submitRegistration(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required', // No constraints on password length or confirmation
            'jabatan' => 'required|in:mahasiswa,konselor,kemahasiswaan,keasramaan,dosen,orang_tua',
        ]);

        try {
            // Create the user in the users table
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->jabatan,
            ]);

            // Insert data into the role-specific table
            switch ($request->jabatan) {
                case 'mahasiswa':
                    Mahasiswa::create([
                        'username' => $user->username,
                        'nim' => $request->nim ?? null, // Optional NIM for students
                    ]);
                    break;

                case 'konselor':
                    Konselor::create([
                        'username' => $user->username,
                        'nip' => $request->nip ?? null, // Optional NIP for konselor
                    ]);
                    break;

                case 'kemahasiswaan':
                    Kemahasiswaan::create([
                        'username' => $user->username,
                        'nip' => $request->nip ?? null, // Optional NIP for kemahasiswaan
                    ]);
                    break;

                case 'dosen':
                    Dosen::create([
                        'username' => $user->username,
                        'nip' => $request->nip ?? null, // Optional NIP for dosen
                    ]);
                    break;

                case 'keasramaan':
                    Keasramaan::create([
                        'username' => $user->username,
                        'nip' => $request->nip ?? null, // Optional NIP for keasramaan
                    ]);
                    break;

                case 'orang_tua':
                    OrangTua::create([
                        'username' => $user->username,
                    ]);
                    break;
            }

            Log::info('User registered successfully:', ['username' => $user->username, 'role' => $user->role]);

            // Redirect to the login section by setting isActive to false
            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dibuat. Silakan login.',
            ]);

        } catch (\Exception $e) {
            Log::error('Registrasi gagal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan, silakan coba lagi.',
            ], 500);
        }
    }
}