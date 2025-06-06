<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnsureStudentDataOrtu
{
    public function handle(Request $request, Closure $next)
    {
        $userRole = $request->session()->get('user.role');
        
        if (in_array($userRole, ['mahasiswa', 'orang_tua'])) {
            if (!$request->session()->has('student_data_ortu')) {
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
                            $request->session()->put('student_data_ortu', $data['data']);
                        } else {
                            Log::error('API request failed:', ['status' => $response->status()]);
                            // Fallback: set default empty data
                            $request->session()->put('student_data', []);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to fetch student data:', ['message' => $e->getMessage()]);
                        // Fallback: set default empty data on exception
                        $request->session()->put('student_data', []);
                    }
                } else {
                    Log::error('Missing API token or NIM in session.');
                    // Fallback: set default empty data even if session data is incomplete
                    $request->session()->put('student_data', []);
                }
            }
        }

        return $next($request);
    }
}
