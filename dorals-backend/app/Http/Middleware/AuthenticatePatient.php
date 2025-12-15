<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePatient
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if patient is in session
        if (session('patient')) {
            return $next($request);
        }

        // Redirect to login if no patient session found
        return redirect()->route('patient.login');
    }
}
