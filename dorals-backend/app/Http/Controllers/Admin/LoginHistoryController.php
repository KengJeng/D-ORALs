<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class LoginHistoryController extends Controller
{
    /**
     * Show login history page (web view)
     */
    public function index(Request $request)
    {
        // Optional: restrict to admins (if using web guard)
        $user = $request->user();
        if ($user && $user instanceof Admin === false) {
            // If not admin, abort or redirect to admin login
            return redirect('/admin/login');
        }

        $perPage = min((int) $request->input('per_page', 20), 200);

        $loginHistory = DB::table('login_history')
            ->select(['login_time', 'user_id', 'user_type', 'ip_address'])
            ->orderBy('login_time', 'desc')
            ->paginate($perPage);

        return view('login-history', [
            'loginHistory' => $loginHistory,
        ]);
    }
}
