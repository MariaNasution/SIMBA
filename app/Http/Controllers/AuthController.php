<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Mahasiswa;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find the user by username
        $user = User::where('username', $request->username)->first();
        if (!$user) {
            Log::warning('Login failed: Username not found', ['username' => $request->username]);
            return back()->withErrors(['login' => 'Username tidak ditemukan.']);
        }

        // Log password details for debugging
        Log::info('Input Password:', ['input' => $request->password]);
        Log::info('Hashed Password in DB:', ['hashed' => $user->password]);

        // Check if the password matches
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Login failed: Incorrect password', ['username' => $user->username]);
            return back()->withErrors(['login' => 'Username atau Password salah.']);
        }

        Log::info('Login successful for user:', ['username' => $user->username, 'role' => $user->role]);

        // Fetch nim based on user role
        $nim = null;
        if ($user->role === 'mahasiswa') {
            $mahasiswa = Mahasiswa::where('username', $user->username)->first();
            $nim = $mahasiswa ? $mahasiswa->nim : null;
            if (!$nim) {
                Log::warning('NIM not found for mahasiswa', ['username' => $user->username]);
            }
        } elseif ($user->role === 'orang_tua') {
            $nim = $user->orangTua?->nim;
            if (!$nim) {
                Log::warning('NIM not found for orang_tua', ['username' => $user->username]);
            }
        }

        // For dosen role, set nip (used in DosenController::beranda)
        $nip = ($user->role === 'dosen') ? $user->username : null;

        // Store hardcoded API token and user data in the session
        session([
            'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IlVOSVFVRS1KV1QtSURFTlRJRklFUiJ9.eyJpc3MiOiJodHRwczpcL1wvYXBpLmV4YW1wbGUuY29tIiwiYXVkIjoiaHR0cHM6XC9cL2Zyb250ZW5kLmV4YW1wbGUuY29tIiwianRpIjoiVU5JUVVFLUpXVC1JREVOVElGSUVSIiwiaWF0IjoxNzQyODAwNDU4LCJleHAiOjE3NDI4MDM0NTgsInVpZCI6MTM5Mn0.R5BRAOJiwcHHCyskvQSnQX6eWhz8u4SYCzZGXSTFYB8',
            'user' => [
                'username' => $user->username,
                'role'     => $user->role,
                'nim'      => $nim, // For mahasiswa and orang_tua roles
                'nip'      => $nip, // For dosen role
            ],
        ]);

        Log::info('Session data set:', [
            'api_token' => session('api_token'),
            'user' => session('user'),
        ]);

        // Redirect based on the user's role
        switch ($user->role) {
            case 'mahasiswa':
                Log::info('Redirecting to mahasiswa route...');
                return redirect()->route('beranda')->with('success', 'Login sebagai mahasiswa berhasil!');

            case 'dosen':
                Log::info('Redirecting to dosen route...');
                return redirect()->route('dosen')->with('success', 'Login sebagai dosen berhasil!');

            case 'keasramaan':
                Log::info('Redirecting to keasramaan route...');
                return redirect()->route('keasramaan')->with('success', 'Login sebagai keasramaan berhasil!');

            case 'orang_tua':
                Log::info('Redirecting to orang_tua route...');
                return redirect()->route('orang_tua')->with('success', 'Login sebagai orang tua berhasil!');

            case 'admin':
                Log::info('Redirecting to admin route...');
                return redirect()->route('admin')->with('success', 'Login sebagai admin berhasil!');

            default:
                Log::warning('Unknown role detected:', ['role' => $user->role]);
                return back()->withErrors(['login' => 'Role tidak dikenali.']);
        }
    }

    public function logout()
    {
        session()->flush(); // Clear all session data
        session()->regenerate(); // Regenerate session ID for security
        return redirect()->route('login');
    }
}