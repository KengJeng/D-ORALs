<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class LoginHistoryController extends Controller
{
    /**
     * List login history (ADMIN ONLY).
     *
     * Optional query parameters:
     * - user_type: 'admin' or 'patient'
     * - user_id: numeric id of the user
     * - date_from: YYYY-MM-DD
     * - date_to: YYYY-MM-DD
     * - per_page: page size (default 20)
     */
    public function index(Request $request)
    {
        // Ensure only admins can access this endpoint
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return response()->json([
                'message' => 'Unauthorized: Admin access only.',
            ], 403);
        }

        // OPTIMIZATION: Select necessary columns only, use indexes for filtering
        $query = DB::table('login_history')->select([
            'login_history_id',
            'user_id',
            'user_type',
            'ip_address',
            'login_time'
        ]);

        // Filter by user type (admin / patient) - uses index
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by specific user id - uses index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range - uses login_time index
        if ($request->filled('date_from')) {
            $query->whereDate('login_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('login_time', '<=', $request->date_to);
        }

        // Default page size with cap
        $perPage = min($request->integer('per_page', 20), 100);

        $history = $query
            ->orderBy('login_time', 'desc')
            ->paginate($perPage);

        return response()->json($history);
    }
}
