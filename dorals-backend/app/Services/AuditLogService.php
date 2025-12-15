<?php

namespace App\Services;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuditLogService
{
    /**
     * Log an action to the audit trail
     */
    public function log($userId, string $action): AuditLog
    {
        // Protect against foreign key constraint: audit_log.user_id references admin.admin_id
        // If the provided userId is not an admin id, store NULL so patient actions don't violate FK.
        $userIdToStore = null;

        if (! is_null($userId)) {
            $exists = DB::table('admin')->where('admin_id', $userId)->exists();
            if ($exists) {
                $userIdToStore = $userId;
            }
        }

        return AuditLog::create([
            'user_id' => $userIdToStore,
            'action' => $action,
            'log_date' => Carbon::now()->toDateString(),
            'log_time' => Carbon::now()->toTimeString(),
        ]);
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs(int $limit = 50)
    {
        return AuditLog::orderBy('log_date', 'desc')
            ->orderBy('log_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by user
     */
    public function getUserLogs($userId, int $limit = 50)
    {
        return AuditLog::byUser($userId)
            ->orderBy('log_date', 'desc')
            ->orderBy('log_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a date range
     */
    public function getLogsByDateRange($startDate, $endDate)
    {
        return AuditLog::dateRange($startDate, $endDate)
            ->orderBy('log_date', 'desc')
            ->orderBy('log_time', 'desc')
            ->get();
    }

    /**
     * Get today's logs
     */
    public function getTodayLogs()
    {
        return AuditLog::today()
            ->orderBy('log_time', 'desc')
            ->get();
    }

    /**
     * Search logs by action
     */
    public function searchLogs(string $action)
    {
        return AuditLog::byAction($action)
            ->orderBy('log_date', 'desc')
            ->orderBy('log_time', 'desc')
            ->get();
    }

    /**
     * Get logs statistics
     */
    public function getStats($startDate = null, $endDate = null)
    {
        $query = AuditLog::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } elseif ($startDate) {
            $query->where('log_date', '>=', $startDate);
        } else {
            $query->today();
        }

        $logs = $query->get();

        return [
            'total_actions' => $logs->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'actions_by_type' => $logs->groupBy(function ($log) {
                // Extract action type (first word)
                return explode(' ', $log->action)[0];
            })->map->count(),
        ];
    }
}