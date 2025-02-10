<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userRole = session('user.role'); // Ambil role dari session
        Log::info('Middleware Role:', ['required_role' => $role, 'user_role' => $userRole]);

        if ($userRole !== $role) {
            return redirect()->route('login')->withErrors(['error' => 'Akses ditolak. Role tidak sesuai.']);
        }
        return $next($request);
    }
}
