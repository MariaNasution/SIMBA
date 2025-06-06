<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnsureStudentData
{
    public function handle(Request $request, Closure $next)
{
    $userRole = $request->session()->get('user.role');
    
    if (in_array($userRole, ['mahasiswa'])) {
        if (!$request->session()->has('student_data')) {
            $apiToken = $request->session()->get('api_token');
            $user = $request->session()->get('user');

            if ($apiToken && $user && isset($user['nim'])) {
                try {
                    $response = Http::withToken($apiToken)
                        ->withOptions(['verify' => false])
                        ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                            'nim' => $user['nim'],
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $request->session()->put('student_data', $data['data']);
                    } else {
                        Log::error('API request failed:', ['status' => $response->status()]);
                        return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data.']);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to fetch student data:', ['message' => $e->getMessage()]);
                    return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
                }
            } else {
                Log::error('Missing API token or NIM in session.');
                return redirect()->route('beranda')->withErrors(['error' => 'Session data tidak lengkap.']);
            }
        }
    }

    return $next($request);
}
}