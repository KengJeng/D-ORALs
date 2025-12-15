<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Check if logged-in user is an Admin instance
        if ($user instanceof Admin) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized: Admin access only.'
        ], 403);
    }
}
