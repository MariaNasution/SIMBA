<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('user')) {
            Log::info('User session not found');
            return redirect()->route('login')->withErrors(['auth' => 'Silakan login terlebih dahulu.']);
        }

        Log::info('Middleware CheckSession: User session found.', $request->session()->get('user'));

        return $next($request);
    }
}
