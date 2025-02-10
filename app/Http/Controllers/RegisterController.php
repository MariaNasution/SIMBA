<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivationToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.registrasi');
    }

    public function submitRegistration(Request $request)
    {
        $request->validate([
            'nim' => 'required|unique:users,nim',
            'password' => 'required|confirmed|min:6',
        ]);

        $nim = $request->nim;
        $password = $request->password;

        try {
            // Mendapatkan email berdasarkan NIM dari API
            $token = $this->getApiToken();
            $studentResponse = Http::withToken($token)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', ['nim' => $nim]);

            $studentData = $studentResponse->json();
            if (!isset($studentData['data']['email'])) {
                return back()->withErrors(['nim' => 'Email untuk NIM ini tidak ditemukan.']);
            }

            $email = $studentData['data']['email'];

            // Generate token aktivasi
            $activationToken = Str::random(64);

            // Simpan data sementara
            ActivationToken::create([
                'nim' => $nim,
                'email' => $email,
                'password' => Hash::make($password),
                'token' => $activationToken,
            ]);

            // Kirim email aktivasi
            Mail::to($email)->send(new \App\Mail\ActivationMail($activationToken));

            return view('auth.waiting-registrasi');
        } catch (\Exception $e) {
            Log::error('Registrasi gagal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }

    private function getApiToken()
    {
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

        $authData = json_decode($response->getBody()->getContents(), true);
        return $authData['token'] ?? null;
    }

    public function showActivationForm()
    {
        return view('auth.activation');
    }

    public function activateAccount(Request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);

        $activationToken = ActivationToken::where('token', $request->token)->first();
        if (!$activationToken) {
            return back()->withErrors(['token' => 'Token tidak valid atau telah digunakan.']);
        }

        // Simpan data user
        User::create([
            'nim' => $activationToken->nim,
            'username' => $activationToken->nim,
            'password' => $activationToken->password,
        ]);

        // Hapus token aktivasi
        $activationToken->delete();

        return redirect()->route('login')->with('success', 'Akun berhasil diaktivasi. Silakan login.');
    }
}
