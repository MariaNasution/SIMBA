<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Mail\ResetPasswordorMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'nim' => 'required',
        ]);

        $nim = $request->nim;

        try {
            // Autentikasi untuk mendapatkan token API
            $client = new \GuzzleHttp\Client(['verify' => false]);

            $authResponse = $client->post('https://cis-dev.del.ac.id/api/jwt-api/do-auth', [
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

            $authData = json_decode($authResponse->getBody()->getContents(), true);

            if (isset($authData['result']) && $authData['result'] === true) {
                $token = $authData['token'];

                // Mendapatkan data mahasiswa berdasarkan NIM
                $studentResponse = Http::withToken($token)
                    ->withOptions(['verify' => false])
                    ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                        'nim' => $nim,
                    ]);

                $studentData = $studentResponse->json();
                Log::info('Memulai proses pengiriman email.');
                if (isset($studentData['data']['email'])) {
                    Log::info('Email ditemukan: ' . $studentData['data']['email']);
                    $email = $studentData['data']['email'];

                    session(['reset_nim' => $nim]);

                    // Generate token reset password
                    $resetToken = Str::random(64);
                    $resetData = PasswordReset::where('email', $email)->first();

                    $resetData = PasswordReset::where('email', $email)->first();

                    if ($resetData) {
                        DB::table('password_resets')
                            ->where('email', $email)
                            ->update([
                                'token' => $resetToken,
                                'updated_at' => now(),
                                'nim' => $nim,
                            ]);
                        Log::info('Data token diperbarui untuk email: ' . $email);
                    } else {
                        PasswordReset::create([
                            'email' => $email,
                            'nim' => $nim,
                            'token' => $resetToken,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        Log::info('Data token baru dibuat untuk email: ' . $email);
                    }

                    Log::info('Token berhasil diperbarui di database untuk email: ' . $email);

                    try {
                        Log::info('Proses pengiriman email dimulai.');
                        Log::info('Token yang dikirim: ' . json_encode(['token' => $resetToken]));
                        Mail::to($email)->send(new ResetPasswordorMail($resetToken));
                        Log::info('Email berhasil dikirim ke: ' . $email);
                    } catch (\Exception $e) {
                        Log::error('Gagal mengirim email: ' . $e->getMessage());
                        return back()->withErrors(['nim' => 'Terjadi kesalahan saat mengirim email: ' . $e->getMessage()]);
                    }

                    Log::info('Email berhasil dikirim.');

                    return redirect()->route('password.waiting-email');
                }

                return back()->withErrors(['nim' => 'Email untuk NIM ini tidak ditemukan.']);
            }

            return back()->withErrors(['nim' => 'Gagal mendapatkan token API.']);
        } catch (\Exception $e) {
            return back()->withErrors(['nim' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
            'token' => 'required',
        ]);

        Log::info('Token yang diterima: ' . $request->token);

        // Cari reset token
        $reset = PasswordReset::where('token', $request->token)->first();
        if (!$reset) {
            Log::error('Token tidak valid atau telah digunakan.');
            return back()->withErrors(['token' => 'Token tidak valid atau telah digunakan.']);
        }

        Log::info('Data reset ditemukan: ', $reset->toArray());

        // Cari user berdasarkan NIM
        $user = User::where('nim', $reset->nim)->first();
        if (!$user) {
            Log::error('User dengan NIM tidak ditemukan: ' . $reset->nim);
            return back()->withErrors(['nim' => 'User dengan NIM ini tidak ditemukan.']);
        }

        Log::info('User ditemukan: ', $user->toArray());

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        Log::info('Password berhasil diubah untuk user dengan NIM: ' . $reset->nim);

        // Hapus token reset
        PasswordReset::where('token', $request->token)->delete();
        session()->forget('reset_nim'); // Hapus NIM dari session jika ada

        return redirect()->route('login')->with('success', 'Password berhasil direset.');
    }
}
