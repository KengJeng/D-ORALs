<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics (Optimized with single query)
     */
    public function stats(Request $request)
{
    $today   = Carbon::today();
    $patient = $request->user(); // logged-in patient via Sanctum

    // Cache per patient per day so patients don't see each other's stats
    $cacheKey = "dashboard_stats_{$patient->patient_id}_{$today->toDateString()}";

    $data = Cache::remember($cacheKey, 300, function () use ($today, $patient) {
        // Base query: this patient's appointments
        $baseQuery = Appointment::where('patient_id', $patient->patient_id);

        // =======================
        // 1. Today's stats
        // =======================
        $todayStats = (clone $baseQuery)
            ->whereDate('scheduled_date', $today)
            ->selectRaw("
                COUNT(CASE WHEN status IN ('Pending', 'Confirmed') THEN 1 END) as today_queue,
                COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_today,
                COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_today,
                COUNT(CASE WHEN status = 'Canceled' THEN 1 END) as canceled_today,
                COUNT(CASE WHEN status = 'No-show' THEN 1 END) as no_show_today
            ")
            ->first();

        // =======================
        // 2. Overall counts by status (for Reports screen)
        // =======================
        $totalAppointments = (clone $baseQuery)->count();
        $pending   = (clone $baseQuery)->where('status', 'Pending')->count();
        $confirmed = (clone $baseQuery)->where('status', 'Confirmed')->count();
        $completed = (clone $baseQuery)->where('status', 'Completed')->count();
        $canceled  = (clone $baseQuery)->where('status', 'Canceled')->count();

        // (Optional) overall patient count – you can keep your original cache
        $patientStats = Cache::remember('patient_counts', 3600, function () {
            return Patient::selectRaw('COUNT(*) as total_patients')->first();
        });

        return [
            // your original fields (still available if you need them)
            'today_queue'      => $todayStats->today_queue      ?? 0,
            'completed_today'  => $todayStats->completed_today  ?? 0,
            'pending_today'    => $todayStats->pending_today    ?? 0,
            'canceled_today'   => $todayStats->canceled_today   ?? 0,
            'no_show_today'    => $todayStats->no_show_today    ?? 0,
            'total_patients'   => $patientStats->total_patients ?? 0,

            // used by Home & Reports screen
            'total_appointments' => $totalAppointments,
            'pending'            => $pending,
            'confirmed'          => $confirmed,
            'completed'          => $completed,
            'canceled'           => $canceled,
        ];
    });

    // cache stores the array; here we wrap it as JSON response
    return response()->json($data);
}


    /**
     * Get appointment trends for analytics (Optimized with date range filling)
     */
    public function trends(Request $request)
    {
        $days = min($request->input('days', 30), 365); // Cap at 365 days
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays($days);

        $cacheKey = "trends_{$days}_{$endDate->toDateString()}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate, $days) {
            $appointments = Appointment::whereBetween('scheduled_date', [$startDate, $endDate])
                ->selectRaw('DATE(scheduled_date) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Fill in missing dates with zero counts
            $labels = [];
            $values = [];
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                $dateString = $currentDate->toDateString();
                $labels[] = $currentDate->format('M d');
                $values[] = $appointments->get($dateString)->count ?? 0;
                $currentDate->addDay();
            }

            return response()->json([
                'labels' => $labels,
                'values' => $values,
                'period' => "{$days} days",
            ]);
        });
    }

    /**
     * Get service utilization statistics (Optimized with eager loading)
     */
    public function serviceUtilization(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->input('end_date', Carbon::today()));

        $cacheKey = "service_utilization_{$startDate->toDateString()}_{$endDate->toDateString()}";

        return Cache::remember($cacheKey, 900, function () use ($startDate, $endDate) {
            // Use subquery for better performance
            $services = Service::select('services.*')
                ->selectSub(function ($query) use ($startDate, $endDate) {
                    $query->from('appointments')
                        ->whereColumn('appointments.service_id', 'services.id')
                        ->whereBetween('scheduled_date', [$startDate, $endDate])
                        ->whereIn('status', ['Confirmed', 'Completed'])
                        ->selectRaw('COUNT(*)');
                }, 'appointments_count')
                ->orderByDesc('appointments_count')
                ->get();

            return response()->json([
                'services' => $services->map(function ($service) {
                    return [
                        'name' => $service->name,
                        'count' => $service->appointments_count ?? 0,
                        'duration' => $service->duration,
                    ];
                }),
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ]);
        });
    }

    /**
     * Get patient demographics (Optimized with single query)
     */
    public function demographics()
    {
        return Cache::remember('patient_demographics', 1800, function () {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            // Single query for all demographic data
            $demographics = Patient::selectRaw("
                COUNT(*) as total_patients,
                COUNT(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 END) as new_this_month,
                sex
            ", [$currentMonth, $currentYear])
                ->groupBy('sex')
                ->get();

            $totalPatients = $demographics->sum('total_patients');
            $newThisMonth = $demographics->first()->new_this_month ?? 0;

            return response()->json([
                'total_patients' => $totalPatients,
                'new_this_month' => $newThisMonth,
                'sex_distribution' => $demographics->map(function ($item) {
                    return [
                        'sex' => $item->sex,
                        'count' => $item->total_patients,
                    ];
                }),
            ]);
        });
    }

    /**
     * Get appointment status breakdown (Optimized)
     */
    public function statusBreakdown(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->input('end_date', Carbon::today()));

        $cacheKey = "status_breakdown_{$startDate->toDateString()}_{$endDate->toDateString()}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            $statusCounts = Appointment::whereBetween('scheduled_date', [$startDate, $endDate])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            return response()->json([
                'breakdown' => $statusCounts->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'count' => $item->count,
                    ];
                }),
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ]);
        });
    }

    /**
     * Get peak hours analysis (Optimized)
     */
    public function peakHours(Request $request)
    {
        $days = min($request->input('days', 30), 365);
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays($days);

        $cacheKey = "peak_hours_{$days}_{$endDate->toDateString()}";

        return Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate, $days) {
            $hourlyData = Appointment::whereBetween('scheduled_date', [$startDate, $endDate])
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->keyBy('hour');

            // Fill in all 24 hours
            $distribution = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $distribution[] = [
                    'hour' => sprintf('%02d:00', $hour),
                    'count' => $hourlyData->get($hour)->count ?? 0,
                ];
            }

            return response()->json([
                'hourly_distribution' => $distribution,
                'period' => "{$days} days",
            ]);
        });
    }

    /**
     * Get overall analytics summary (Optimized with single query)
     */
    public function analyticsSummary(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', Carbon::today()->subDays(30)));
        $endDate = Carbon::parse($request->input('end_date', Carbon::today()));

        $cacheKey = "analytics_summary_{$startDate->toDateString()}_{$endDate->toDateString()}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            // Single optimized query for all metrics
            $summary = Appointment::whereBetween('scheduled_date', [$startDate, $endDate])
                ->selectRaw("
                    COUNT(*) as total_appointments,
                    COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'Canceled' THEN 1 END) as canceled,
                    COUNT(CASE WHEN status = 'No-show' THEN 1 END) as no_show
                ")
                ->first();

            $totalAppointments = $summary->total_appointments ?? 0;
            $completed = $summary->completed ?? 0;
            $canceled = $summary->canceled ?? 0;
            $noShow = $summary->no_show ?? 0;

            $completionRate = $totalAppointments > 0 
                ? round(($completed / $totalAppointments) * 100, 2) 
                : 0;

            $cancellationRate = $totalAppointments > 0 
                ? round((($canceled + $noShow) / $totalAppointments) * 100, 2) 
                : 0;

            $daysDiff = max($startDate->diffInDays($endDate), 1);
            $averagePerDay = $totalAppointments > 0 
                ? round($totalAppointments / $daysDiff, 2) 
                : 0;

            return response()->json([
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'total_appointments' => $totalAppointments,
                'completed' => $completed,
                'canceled' => $canceled,
                'no_show' => $noShow,
                'completion_rate' => $completionRate,
                'cancellation_rate' => $cancellationRate,
                'average_per_day' => $averagePerDay,
            ]);
        });
    }

    /**
     * Clear dashboard cache (useful for admin actions)
     */
    public function clearCache()
    {
        Cache::flush(); // Or use tags if available: Cache::tags(['dashboard'])->flush();
        
        return response()->json([
            'message' => 'Dashboard cache cleared successfully'
        ]);
    }

    /**
     * Return upcoming appointments for the authenticated patient
     */
public function upcoming(Request $request)
{
    $patient = $request->user();

    if (! $patient) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $appointments = Appointment::select([
            'appointment_id',
            'patient_id',
            'scheduled_date',
            'status',
            'queue_number',
            'created_at',
        ])
        ->with('services:service_id,name,duration') // same shape as myAppointments
        ->where('patient_id', $patient->patient_id)
        ->whereDate('scheduled_date', '>=', Carbon::today())
        ->whereIn('status', ['Pending', 'Confirmed'])
        ->orderBy('scheduled_date', 'asc')
        ->orderBy('queue_number')
        ->limit(10)
        ->get();

    return response()->json($appointments);
}


    /**
     * Return services list for patient UI (lightweight)
     */
    public function services(Request $request)
    {
        // Cache short-lived for quick responses
        $services = Cache::remember('public_services_list', 300, function () {
            return Service::select('service_id', 'name', 'duration')
                ->orderBy('name')
                ->get();
        });

        return response()->json([
            'services' => $services,
        ]);
    }

    /**
     * Return user-specific summary data for patient UI
     */
    public function my(Request $request)
    {
        $patient = $request->user();

        if (! $patient) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Basic patient info
        $patientData = Patient::select('patient_id', 'first_name', 'middle_name', 'last_name', 'email', 'contact_no')
            ->find($patient->patient_id);

        // Upcoming count and last appointment
        $today = Carbon::today();

        $upcomingCount = Appointment::where('patient_id', $patient->patient_id)
            ->whereDate('scheduled_date', '>=', $today)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->count();

        $lastAppointment = Appointment::select(['appointment_id', 'scheduled_date', 'status'])
            ->where('patient_id', $patient->patient_id)
            ->orderBy('scheduled_date', 'desc')
            ->first();

        return response()->json([
            'patient' => $patientData,
            'upcoming_count' => $upcomingCount,
            'last_appointment' => $lastAppointment,
        ]);
    }
}