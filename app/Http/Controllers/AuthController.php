<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Rules\ReCaptcha;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        Log::info('Environment debug:', [
            'RECAPTCHA_SITE_KEY' => env('RECAPTCHA_SITE_KEY'),
            'RECAPTCHA_SECRET_KEY' => env('RECAPTCHA_SECRET_KEY'),
            'RECAPTCHA_ENABLED' => env('RECAPTCHA_ENABLED', true),
            'config_recaptcha_site_key' => config('services.recaptcha.site_key'),
            'config_recaptcha_secret_key' => config('services.recaptcha.secret_key'),
            'config_recaptcha_enabled' => config('services.recaptcha.enabled'),
            'APP_ENV' => env('APP_ENV'),
        ]);
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('Login request received:', ['request' => $request->all()]);

        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        // Add reCAPTCHA validation only if enabled
        if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key')) {
            $rules['g-recaptcha-response'] = ['required', new ReCaptcha];
        }

        $request->validate($rules, [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA checkbox.',
            'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
        ]);

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            Log::warning('Login failed: Invalid username', ['username' => $request->username]);
            return redirect()->route('login')->withErrors(['login' => 'Nama pengguna tidak valid.']);
        }

        Log::info('Input Password:', ['input' => $request->password]);
        Log::info('Hashed Password in DB:', ['hashed' => $user->password]);

        if ($user && Hash::check($request->password, $user->password)) {
            Log::info('Password match');
            Log::info('Login berhasil untuk user:', ['username' => $user->username, 'role' => $user->role]);

            auth()->login($user);

            $apiToken = null;
            $data = null;

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
                    $apiToken = $data['token'];
                    Log::info('Token API diterima:', ['token' => $apiToken]);
                } else {
                    Log::error('API login gagal, response tidak valid, melanjutkan dengan login lokal', ['response_parsed' => $data]);
                }
            } catch (\Exception $e) {
                Log::error('API Error, melanjutkan dengan login lokal:', ['message' => $e->getMessage()]);
            }

            // Initialize session data
            $sessionData = [
                'username' => $user->username,
                'role' => $user->role,
            ];

            // Role-specific session data
            if ($user->role === 'mahasiswa') {
                $mahasiswa = Mahasiswa::where('username', $user->username)->first();
                $sessionData['nim'] = $mahasiswa ? $mahasiswa->nim : null;
            } elseif ($user->role === 'dosen') {
                // Fetch dosen data from the API (similar to fetchDosenData in DosenController)
                $baseUrl = 'https://cis-dev.del.ac.id';
                $dosenResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->timeout(15)
                    ->get("{$baseUrl}/api/library-api/dosen", ['nip' => $user->username]);

                if (!$dosenResponse->successful()) {
                    Log::error('Failed to fetch dosen data during login', [
                        'status' => $dosenResponse->status(),
                        'response' => $dosenResponse->body(),
                    ]);
                    return redirect()->route('login')->withErrors(['login' => 'Failed to fetch lecturer data.']);
                }

                $dosenData = $dosenResponse->json();
                $dosenSession = $dosenData['data']['dosen'][0];

                // Set the full dosen session data
                $sessionData = [
                    "username" => $user->username,
                    "role" => 'dosen',
                    "pegawai_id" => $dosenSession['pegawai_id'],
                    "dosen_id" => $dosenSession['dosen_id'],
                    "nip" => $dosenSession['nip'],
                    "nama" => $dosenSession['nama'],
                    "email" => $dosenSession['email'],
                    "prodi_id" => $dosenSession['prodi_id'],
                    "prodi" => $dosenSession['prodi'],
                    "jabatan_akademik" => $dosenSession['jabatan_akademik'],
                    "jabatan_akademik_desc" => $dosenSession['jabatan_akademik_desc'],
                    "jenjang_pendidikan" => $dosenSession['jenjang_pendidikan'],
                    "nidn" => $dosenSession['nidn'],
                    "user_id" => $dosenSession['user_id'],
                ];
            } elseif ($user->role === 'orang_tua') {
                $sessionData['nim'] = $user->orangTua?->nim;
            }

            session([
                'api_token' => $apiToken,
                'user_api' => $data['user'] ?? null,
                'user' => $sessionData,
            ]);

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

                case 'kemahasiswaan':
                    Log::info('Redirecting to kemahasiswaan route...');
                    return redirect()->route('kemahasiswaan_beranda')->with('success', 'Login sebagai kemahasiswaan berhasil!');

                case 'konselor':
                    Log::info('Redirecting to konselor route...');
                    return redirect()->route('konselor_beranda')->with('success', 'Login sebagai konselor berhasil!');

                case 'admin':
                    Log::info('Redirecting to admin route...');
                    return redirect()->route('admin.beranda')->with('success', 'Login sebagai admin berhasil!');

                default:
                    Log::warning('Unknown role detected:', ['role' => $user->role]);
                    return back()->withErrors(['login' => 'Role tidak dikenali.']);
            }
        }

        Log::warning('Login failed: Incorrect password', ['username' => $request->username]);
        return back()->withErrors(['login2' => 'Password salah.']);
    }

    public function logout()
    {
        session()->flush();
        session()->regenerate();

        return redirect()->route('login');
    }
}