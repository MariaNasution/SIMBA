<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['login' => 'NIM tidak ditemukan.']);
        }

        Log::info('Input Password:', ['input' => $request->password]);
        Log::info('Hashed Password in DB:', ['hashed' => $user->password]);

        if ($user && Hash::check($request->password, $user->password)) {
            session(['user' => ['nim' => $user->nim, 'name' => $user->username, 'role' => $user->role]]);
            Log::info('Role user yang login:', ['role' => $user->role]);
            Log::info('Password match');
            Log::info('Login berhasil untuk user:', ['nim' => $user->nim, 'role' => $user->role]);

            try {
                Log::info('Mengirim permintaan API eksternal...');
                $client = new \GuzzleHttp\Client(['verify' => false]);

                $response = $client->post('https://cis-dev.del.ac.id/api/jwt-api/do-auth', [
                    'form_params' => [
                        'username' => 'johannes',
                        'password' => 'Del@2022',
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'stream' => true,
                    'timeout' => 60,
                ]);

                $body = $response->getBody()->getContents();
                Log::info('Respons API diterima (mentah):', ['response_raw' => $body]);

                $data = json_decode($body, true);
                Log::info('Respons API setelah diuraikan:', ['parsed_response' => $data]);

                if ($data && isset($data['result']) && $data['result'] === true) {
                    session(['api_token' => $data['token']]);
                    session(['user_api' => $data['user']]);
                    Log::info('Token API diterima:', ['token' => $data['token']]);

                    if ($user->role === 'admin') {
                        Log::info('Redirecting to admin route...');
                        return redirect()->route('admin')->with('success', 'Login sebagai admin berhasil!');
                    } else {
                        return redirect()->route('beranda')->with('success', 'Login berhasil!');
                    }
                }

                Log::error('API login gagal', ['response_parsed' => $data]);
                return back()->withErrors(['login' => 'Gagal mendapatkan token API.']);
            } catch (\Exception $e) {
                Log::error('API Error:', ['message' => $e->getMessage()]);
                return back()->withErrors(['login' => 'Terjadi kesalahan saat menghubungi API.']);
            }
        }

        // Jika autentikasi gagal
        return back()->withErrors(['login' => 'NIM atau Password salah.']);
    }

    public function logout()
    {
        session()->flush(); // Hapus semua data session
        session()->regenerate(); // Regenerate session ID untuk keamanan
        return redirect()->route('login');
    }
}
