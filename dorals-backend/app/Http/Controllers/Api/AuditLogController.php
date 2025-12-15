<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('audit_log')
                ->leftJoin('admin', 'audit_log.user_id', '=', 'admin.admin_id')
                ->leftJoin('patients', 'audit_log.user_id', '=', 'patients.patient_id')
                ->select([
                    'audit_log.log_id as id',
                    'audit_log.user_id',
                    'audit_log.action',
                    'audit_log.log_date',
                    'audit_log.log_time',
                    DB::raw('CASE 
                        WHEN admin.name IS NOT NULL THEN admin.name
                        WHEN patients.first_name IS NOT NULL THEN CONCAT(patients.first_name, " ", patients.last_name)
                        ELSE "System"
                    END as user_name')
                ]);
            
            // Filter by action type
            if ($request->filled('filter') && $request->filter !== 'all') {
                $query->where('audit_log.action', 'like', "%{$request->filter}%");
            }
            
            // Order by date and time
            $query->orderByRaw('audit_log.log_date DESC, audit_log.log_time DESC');
            
            // Pagination
            $page = (int) $request->get('page', 1);
            $perPage = 20;
            
            // Get total count
            $total = DB::table('audit_log')
                ->when($request->filled('filter') && $request->filter !== 'all', function($q) use ($request) {
                    $q->where('action', 'like', "%{$request->filter}%");
                })
                ->count();
            
            $results = $query->offset(($page - 1) * $perPage)
                            ->limit($perPage)
                            ->get();
            
            $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
            
            return response()->json([
                'current_page' => $page,
                'data' => $results,
                'first_page_url' => "?page=1",
                'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'last_page' => $lastPage,
                'last_page_url' => "?page=" . $lastPage,
                'next_page_url' => $page < $lastPage ? "?page=" . ($page + 1) : null,
                'path' => $request->url(),
                'per_page' => $perPage,
                'prev_page_url' => $page > 1 ? "?page=" . ($page - 1) : null,
                'to' => min($page * $perPage, $total),
                'total' => $total,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Audit logs error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to load audit logs',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
    
    public function stats()
    {
        try {
            $today = Carbon::today();
            $cacheKey = "audit_stats_{$today->toDateString()}";

            return Cache::remember($cacheKey, 600, function () use ($today) {
                $stats = DB::table('audit_log')
                    ->whereDate('log_date', $today)
                    ->selectRaw("
                        COUNT(*) as today_count,
                        COUNT(CASE WHEN action NOT LIKE '%system%' THEN 1 END) as user_actions,
                        COUNT(CASE WHEN action LIKE '%system%' THEN 1 END) as system_events,
                        COUNT(DISTINCT user_id) as active_users
                    ")
                    ->first();

                return [
                    'today_count' => $stats->today_count ?? 0,
                    'user_actions' => $stats->user_actions ?? 0,
                    'system_events' => $stats->system_events ?? 0,
                    'active_users' => $stats->active_users ?? 0,
                ];
            });
            
        } catch (\Exception $e) {
            Log::error('Audit stats error: ' . $e->getMessage());
            
            return response()->json([
                'today_count' => 0,
                'user_actions' => 0,
                'system_events' => 0,
                'active_users' => 0,
            ]);
        }
    }
}