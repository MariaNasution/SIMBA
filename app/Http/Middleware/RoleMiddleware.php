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
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Get the user's role from the session
        $userRole = session('user.role');
        Log::info('Middleware Role:', ['required_roles' => $roles, 'user_role' => $userRole]);

        // Check if the user's role matches any of the allowed roles
        if (!in_array($userRole, $roles)) {
            return redirect()->route('login')->withErrors(['error' => 'Akses ditolak. Role tidak sesuai.']);
        }

        return $next($request);
    }
}