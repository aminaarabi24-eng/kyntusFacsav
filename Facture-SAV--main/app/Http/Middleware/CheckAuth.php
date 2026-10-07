<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('is_authenticated')) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter d\'abord.');
        }

        return $next($request);
    }
}