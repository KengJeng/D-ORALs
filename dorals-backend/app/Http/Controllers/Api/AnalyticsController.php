<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;



class AnalyticsController extends Controller
{
    public function appointments()
    {
        $totalAppointments = Appointment::count();
        $completed = Appointment::where('status', 'Completed')->count();
        $canceled = Appointment::whereIn('status', ['Canceled', 'No-show'])->count();
        
        $completionRate = $totalAppointments > 0 ? round(($completed / $totalAppointments) * 100, 1) : 0;
        $cancellationRate = $totalAppointments > 0 ? round(($canceled / $totalAppointments) * 100, 1) : 0;
        
        $firstAppointment = Appointment::min('scheduled_date');
        $daysOperating = $firstAppointment ? Carbon::parse($firstAppointment)->diffInDays(Carbon::now()) + 1 : 1;
        $avgPerDay = round($totalAppointments / $daysOperating, 1);
        
        $trends = $this->getAppointmentTrends(30);
        
        $statusBreakdown = $this->getStatusBreakdown();
        
        $peakDays = $this->getPeakDays();
        
        $monthly = $this->getMonthlyComparison();
        
        return response()->json([
            'total_appointments' => $totalAppointments,
            'completion_rate' => $completionRate,
            'cancellation_rate' => $cancellationRate,
            'avg_per_day' => $avgPerDay,
            'trends' => $trends,
            'status_breakdown' => $statusBreakdown,
            'peak_days' => $peakDays,
            'monthly' => $monthly
        ]);
    }
    
    public function demographics()
    {
        $totalPatients = Patient::count();
        $newThisMonth = Patient::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $activePatients = Patient::has('appointments')->count();
        
        $totalAppointments = Appointment::count();
        $avgVisits = $activePatients > 0 ? round($totalAppointments / $activePatients, 1) : 0;
        
        $gender = $this->getGenderDistribution();
        
        $barangays = $this->getBarangayDistribution();
        
        $growth = $this->getPatientGrowth();
        
        $services = $this->getServiceUtilization();
        
        return response()->json([
            'total_patients' => $totalPatients,
            'new_this_month' => $newThisMonth,
            'active_patients' => $activePatients,
            'avg_visits' => $avgVisits,
            'gender' => $gender,
            'barangays' => $barangays,
            'growth' => $growth,
            'services' => $services
        ]);
    }
    
    private function getAppointmentTrends($days)
    {
        $startDate = Carbon::now()->subDays($days);
        $appointments = Appointment::selectRaw('DATE(scheduled_date) as date, COUNT(*) as count')
            ->where('scheduled_date', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($appointments as $apt) {
            $labels[] = Carbon::parse($apt->date)->format('M d');
            $values[] = $apt->count;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    private function getStatusBreakdown()
    {
        $statuses = Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($statuses as $status) {
            $labels[] = $status->status;
            $values[] = $status->count;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    private function getPeakDays()
    {
        $days = array_fill(0, 7, 0);
        
        $appointments = Appointment::selectRaw('DAYOFWEEK(scheduled_date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->get();
        
        foreach ($appointments as $apt) {
            $dayIndex = ($apt->day + 5) % 7;
            $days[$dayIndex] = $apt->count;
        }
        
        return $days;
    }
    
    private function getMonthlyComparison()
    {
        $months = Appointment::selectRaw('DATE_FORMAT(scheduled_date, "%Y-%m") as month, COUNT(*) as count')
            ->where('scheduled_date', '>=', Carbon::now()->subMonths(3))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($months as $month) {
            $labels[] = Carbon::parse($month->month . '-01')->format('M Y');
            $values[] = $month->count;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    private function getGenderDistribution()
    {
        $genders = Patient::select('sex', DB::raw('COUNT(*) as count'))
            ->groupBy('sex')
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($genders as $gender) {
            $labels[] = $gender->sex;
            $values[] = $gender->count;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    private function getBarangayDistribution()
{
    $barangays = Patient::selectRaw("
            CASE 
                WHEN address IS NULL OR address = '' THEN 'Unknown'
                WHEN LOCATE(',', address) = 0 THEN TRIM(address)
                ELSE SUBSTRING_INDEX(address, ',', 1)
            END as barangay,
            COUNT(*) as count
        ")
        ->groupBy('barangay')
        ->orderByDesc('count')
        ->limit(10)
        ->get();

    $labels = [];
    $values = [];

    foreach ($barangays as $brgy) {
        $labels[] = str_replace('Barangay ', 'Brgy. ', trim($brgy->barangay));
        $values[] = $brgy->count;
    }

    return [
        'labels' => $labels,
        'values' => $values
    ];
}

    
    private function getPatientGrowth()
    {
        $months = Patient::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($months as $month) {
            $labels[] = Carbon::parse($month->month . '-01')->format('M Y');
            $values[] = $month->count;
        }
        
        return ['labels' => $labels, 'values' => $values];
    }
    
    private function getServiceUtilization()
{
    $servicesTable = 'services';
    $pivotTable = 'appointment_services';

    $services = DB::table($servicesTable)
        ->leftJoin($pivotTable, $servicesTable . '.service_id', '=', $pivotTable . '.service_id')
        ->select(
            $servicesTable . '.name',
            DB::raw('COUNT(' . $pivotTable . '.service_id) as count')
        )
        ->groupBy($servicesTable . '.service_id', $servicesTable . '.name')
        ->orderByDesc('count')
        ->limit(10)
        ->get();

    $labels = [];
    $values = [];

    foreach ($services as $service) {
        $labels[] = $service->name;
        $values[] = $service->count;
    }

    return [
        'labels' => $labels,
        'values' => $values,
    ];
}

    public function appointmentsForecast(Request $request)
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(90);

        $historical = Appointment::select(
                DB::raw('DATE(scheduled_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('scheduled_date', '>=', $startDate)
            ->whereDate('scheduled_date', '<=', $today)
            ->whereNotIn('status', ['Canceled', 'No-show'])
            ->groupBy(DB::raw('DATE(scheduled_date)'))
            ->orderBy('date', 'asc')
            ->get();

        $weekdayCounts = [];
        $weekdayTotals = [];
        $weekdayDays = [];

        foreach ($historical as $row) {
            $date = Carbon::parse($row->date);
            $weekday = $date->dayOfWeekIso; 

            if (!isset($weekdayTotals[$weekday])) {
                $weekdayTotals[$weekday] = 0;
                $weekdayDays[$weekday] = 0;
            }

            $weekdayTotals[$weekday] += $row->count;
            $weekdayDays[$weekday] += 1;
        }

        $weekdayAverages = [];
        for ($w = 1; $w <= 7; $w++) {
            if (!empty($weekdayDays[$w])) {
                $weekdayAverages[$w] = round($weekdayTotals[$w] / $weekdayDays[$w], 2);
            } else {
                $weekdayAverages[$w] = $historical->avg('count') ?: 0;
            }
        }

        $forecastDays = 7;
        $forecastLabels = [];
        $forecastValues = [];

        for ($i = 1; $i <= $forecastDays; $i++) {
            $date = $today->copy()->addDays($i);
            $weekday = $date->dayOfWeekIso;
            $forecastLabels[] = $date->toDateString();
            $forecastValues[] = (float) ($weekdayAverages[$weekday] ?? 0);
        }

        $historicalForChart = $historical->filter(function ($row) use ($today) {
                return Carbon::parse($row->date)->greaterThanOrEqualTo($today->copy()->subDays(30));
            })
            ->values();

        $historicalLabels = $historicalForChart->pluck('date')->map(function ($d) {
            return Carbon::parse($d)->toDateString();
        })->toArray();

        $historicalValues = $historicalForChart->pluck('count')->map(fn($c) => (int) $c)->toArray();

        return response()->json([
            'historical' => [
                'labels' => $historicalLabels,
                'values' => $historicalValues,
            ],
            'forecast' => [
                'labels' => $forecastLabels,
                'values' => $forecastValues,
            ],
        ]);
    }

    public function calendarDensity(Request $request)
    {
        $year  = (int) ($request->query('year', now()->year));
        $month = (int) ($request->query('month', now()->month));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = (clone $startOfMonth)->endOfMonth();

        $rows = Appointment::whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(scheduled_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row->day] = (int) $row->total;
        }

        return response()->json([
            'year'      => $year,
            'month'     => $month,
            'start'     => $startOfMonth->toDateString(),
            'end'       => $endOfMonth->toDateString(),
            'counts_by_day' => $byDay,
        ]);
    }

}
