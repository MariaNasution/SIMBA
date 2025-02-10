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
        if (!$request->session()->has('student_data')) {
            $apiToken = $request->session()->get('api_token');
            $user = $request->session()->get('user');

            if ($apiToken && $user) {
                try {
                    $response = Http::withToken($apiToken)
                        ->withOptions(['verify' => false])
                        ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                            'nim' => $user['nim'],
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $request->session()->put('student_data', $data['data']);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to fetch student data:', ['message' => $e->getMessage()]);
                }
            }
        }

        return $next($request);
    }
}
