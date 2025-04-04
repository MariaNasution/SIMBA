<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnsureStudentDataAllStudent
{
    public function handle(Request $request, Closure $next)
    {
        $userRole = $request->session()->get('user.role');
        
        if (in_array($userRole, ['keasramaan'])) {
            if (!$request->session()->has('student_data_all_student')) {
                $apiToken = $request->session()->get('api_token');
                $user = $request->session()->get('user');

                if ($apiToken && $user) {
                    try {
                        // Tambahkan parameter status = 'Aktif'
                        $response = Http::withToken($apiToken)
                            ->withOptions(['verify' => false])
                            ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa', [
                                'status' => 'Aktif',
                            ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            $request->session()->put('student_data_all_student', $data['data']);
                        } else {
                            Log::error('API request failed:', ['status' => $response->status()]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to fetch student data:', ['message' => $e->getMessage()]);
                    }
                } else {
                    Log::error('Missing API token or user data in session.');
                }
            }
        }

        return $next($request);
    }
}
