<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index()
    {
        // CONFIG
        $historicalDays  = 90;  // how many days of history to show
        $forecastHorizon = 7;   // how many days to forecast
        $movingWindow    = 7;   // moving average window (days)

        $today        = Carbon::today();
        $historyStart = $today->copy()->subDays($historicalDays - 1);

        // =======================================
        // 1. Historical daily appointment counts
        // =======================================
        $rawHistory = Appointment::selectRaw('DATE(scheduled_date) as date, COUNT(*) as total')
            ->whereDate('scheduled_date', '>=', $historyStart)
            ->whereDate('scheduled_date', '<=', $today)
            ->where('status', 'Completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $historicalLabels = [];
        $historicalValues = [];

        $loopDate = $historyStart->copy();
        while ($loopDate->lte($today)) {
            $dateStr = $loopDate->toDateString();
            $historicalLabels[] = $dateStr;
            $historicalValues[] = isset($rawHistory[$dateStr])
                ? (int) $rawHistory[$dateStr]->total
                : 0;
            $loopDate->addDay();
        }

        // ==================================
        // 2. Simple moving-average forecast
        // ==================================
        $forecastLabels = [];
        $forecastValues = [];

        $historyForForecast = $historicalValues;

        for ($i = 1; $i <= $forecastHorizon; $i++) {
            $forecastDate     = $today->copy()->addDays($i);
            $forecastLabels[] = $forecastDate->toDateString();

            $windowSize = min($movingWindow, count($historyForForecast));
            if ($windowSize === 0) {
                $forecast = 0;
            } else {
                $window   = array_slice($historyForForecast, -$windowSize);
                $forecast = round(array_sum($window) / $windowSize);
            }

            $forecastValues[]     = $forecast;
            $historyForForecast[] = $forecast;
        }

        // ==================================================
        // 3. Combined chart data (history + forecast together)
        // ==================================================
        $combinedLabels     = array_merge($historicalLabels, $forecastLabels);
        $combinedHistorical = [];
        $combinedForecast   = [];

        foreach ($combinedLabels as $index => $label) {
            if ($index < count($historicalLabels)) {
                $combinedHistorical[] = $historicalValues[$index];
                $combinedForecast[]   = null;
            } else {
                $forecastIndex        = $index - count($historicalLabels);
                $combinedHistorical[] = null;
                $combinedForecast[]   = $forecastValues[$forecastIndex];
            }
        }

        // ================================
        // 4. Service "forecast" (simple)
        // ================================
        $serviceWindowStart = $today->copy()->subDays(30);

        $serviceData = DB::table('appointment_services')
            ->join('appointments', 'appointment_services.appointment_id', '=', 'appointments.appointment_id')
            ->join('services', 'appointment_services.service_id', '=', 'services.service_id')
            ->whereDate('appointments.scheduled_date', '>=', $serviceWindowStart)
            ->whereDate('appointments.scheduled_date', '<=', $today)
            ->where('appointments.status', 'Completed')
            ->select('services.name as service_name', DB::raw('COUNT(*) as total'))
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->get();

        $serviceForecast = [
            'labels' => $serviceData->pluck('service_name')->toArray(),
            'values' => $serviceData->pluck('total')->toArray(),
        ];

        // ===========================================
        // 5. Forecast summary metrics for the cards
        // ===========================================
        $totalNextPeriod = array_sum($forecastValues);

        $busiestDayValue = 0;
        $busiestDayLabel = null;
        foreach ($forecastValues as $i => $value) {
            if ($value > $busiestDayValue) {
                $busiestDayValue = $value;
                $busiestDayLabel = $forecastLabels[$i];
            }
        }

        $trendDirection   = 'Stable';
        $trendDescription = 'Trend based on recent appointment patterns.';

        $n = count($historicalValues);
        if ($n >= 14) {
            $recent7   = array_slice($historicalValues, -7);
            $prev7     = array_slice($historicalValues, -14, 7);
            $avgRecent = array_sum($recent7) / 7;
            $avgPrev   = array_sum($prev7) / 7;

            if ($avgPrev > 0) {
                $changePct = (($avgRecent - $avgPrev) / $avgPrev) * 100;

                if ($changePct > 5) {
                    $trendDirection = 'Increasing';
                } elseif ($changePct < -5) {
                    $trendDirection = 'Decreasing';
                }

                $trendDescription = sprintf(
                    'Average daily appointments changed by %.1f%% compared to the previous week.',
                    $changePct
                );
            }
        }

        $cancelWindowStart = $today->copy()->subDays(30);

        $completedCount = Appointment::whereDate('scheduled_date', '>=', $cancelWindowStart)
            ->whereDate('scheduled_date', '<=', $today)
            ->where('status', 'Completed')
            ->count();

        $canceledCount = Appointment::whereDate('scheduled_date', '>=', $cancelWindowStart)
            ->whereDate('scheduled_date', '<=', $today)
            ->where('status', 'Canceled')
            ->count();

        $cancellationRate = 0;
        if (($completedCount + $canceledCount) > 0) {
            $cancellationRate = $canceledCount / ($completedCount + $canceledCount);
        }

        $expectedCancellations = round($totalNextPeriod * $cancellationRate);

        $topService = $serviceData->first()->service_name ?? 'High-demand services';

        $forecastSummary = [
            'total_next_period'      => $totalNextPeriod,
            'busiest_day_label'      => $busiestDayLabel,
            'busiest_day_value'      => $busiestDayValue,
            'trend_direction'        => $trendDirection,
            'trend_description'      => $trendDescription,
            'expected_cancellations' => $expectedCancellations,
            'top_service'            => $topService,
        ];
// ===========================================
// 6. Prescriptive Scheduling Recommendations
// ===========================================
$dentistsAvailable    = 1;   // adjust as needed
$avgServiceDuration   = 30;  // default minutes
$peakCongestionWindow = "10:00–11:30 AM";

// Average daily load from forecast
$avgForecastPerDay = $forecastHorizon > 0
    ? round($totalNextPeriod / $forecastHorizon, 1)
    : 0;

// No-show risk (last 30 days)
$recentWindow = Carbon::today()->subDays(30);

$recentCompleted = Appointment::whereDate('scheduled_date', '>=', $recentWindow)
    ->where('status', 'Completed')
    ->count();

$recentNoShow = Appointment::whereDate('scheduled_date', '>=', $recentWindow)
    ->where('status', 'No-show')
    ->count();

$noShowRate = ($recentCompleted + $recentNoShow) > 0
    ? round(($recentNoShow / ($recentCompleted + $recentNoShow)) * 100, 1)
    : 0;

// =========================================
// Suggested day-of-week logic (prescriptive)
// =========================================
$weekdayNames = [
    1 => 'Sunday',
    2 => 'Monday',
    3 => 'Tuesday',
    4 => 'Wednesday',
    5 => 'Thursday',
    6 => 'Friday',
    7 => 'Saturday',
];

// Look at last 6 weeks
$historyWindowStart = $today->copy()->subWeeks(6);

$weekdayStats = Appointment::selectRaw('
        DAYOFWEEK(scheduled_date) as dow,
        COUNT(*) as total,
        SUM(CASE WHEN status = "No-show" THEN 1 ELSE 0 END) as no_shows
    ')
    ->whereDate('scheduled_date', '>=', $historyWindowStart)
    ->whereDate('scheduled_date', '<=', $today)
    ->groupBy('dow')
    ->get()
    ->keyBy('dow');

$weeks         = max(1, $historyWindowStart->diffInWeeks($today));
$weekdayScores = [];
$weekdayLoad   = [];
$weekdayNoShow = [];

// Compute avg load & no-show per weekday
foreach ($weekdayNames as $dow => $name) {
    $row = $weekdayStats->get($dow);

    $total   = $row->total    ?? 0;
    $noShows = $row->no_shows ?? 0;

    $avgLoad = $weeks > 0 ? $total / $weeks : 0;
    $nsRate  = ($total > 0) ? ($noShows / $total) * 100 : 0;

    $weekdayLoad[$dow]   = $avgLoad;
    $weekdayNoShow[$dow] = $nsRate;
}

// Normalize scoring: lower load + lower no-show = better
$maxLoad   = max($weekdayLoad ?: [0]);
$maxNoShow = max($weekdayNoShow ?: [0]);

foreach ($weekdayNames as $dow => $name) {
    $load   = $weekdayLoad[$dow]   ?? 0;
    $nsRate = $weekdayNoShow[$dow] ?? 0;

    $loadComponent   = $maxLoad > 0   ? (1 - ($load / $maxLoad)) * 0.6 : 0;
    $noShowComponent = $maxNoShow > 0 ? (1 - ($nsRate / $maxNoShow)) * 0.4 : 0;

    $weekdayScores[$dow] = $loadComponent + $noShowComponent;
}

// Best and worst weekdays
$bestDow = !empty($weekdayScores)
    ? array_keys($weekdayScores, max($weekdayScores))[0]
    : 2; // default Monday

$worstDow = !empty($weekdayScores)
    ? array_keys($weekdayScores, min($weekdayScores))[0]
    : 2; // default Monday

$suggestedDayName = $weekdayNames[$bestDow];
$worstDayName     = $weekdayNames[$worstDow];

// ============================
// Build full ranking list
// ============================
$ranking = [];

// convert scores → readable structure
foreach ($weekdayScores as $dow => $score) {
    $ranking[] = [
        'day'   => $weekdayNames[$dow],
        'score' => round($score, 4),
        'load'  => round($weekdayLoad[$dow] ?? 0, 2),
        'no_show_rate' => round($weekdayNoShow[$dow] ?? 0, 1)
    ];
}

// Sort descending (best → worst)
usort($ranking, function($a, $b) {
    return $b['score'] <=> $a['score'];
});


// Build final prescriptive data
$prescriptiveReco = [
    'peak_window'          => $peakCongestionWindow,
    'avg_daily_load'       => $avgForecastPerDay,
    'no_show_risk'         => $noShowRate,
    'suggested_day'        => $suggestedDayName,
    'worst_day'            => $worstDayName,
    'dentists_available'   => $dentistsAvailable,
    'avg_service_duration' => $avgServiceDuration,
    'message'              => "Peak congestion expected at {$peakCongestionWindow}. "
                            . "Best day for scheduling is {$suggestedDayName} to minimize crowding and no-shows. "
                            . "Avoid scheduling on {$worstDayName} when possible due to higher traffic and no-show patterns.",
];



$appointments = Appointment::with('patient')
    ->orderBy('scheduled_date', 'asc')
    ->take(20)
    ->get();    
        // ===========================================
        // 7. Return view with all data
        // ===========================================
        return view('admin.dashboard', [
            'historicalLabels'   => $historicalLabels,
            'historicalValues'   => $historicalValues,

            'forecastLabels'     => $forecastLabels,
            'forecastValues'     => $forecastValues,

            'combinedLabels'     => $combinedLabels,
            'combinedHistorical' => $combinedHistorical,
            'combinedForecast'   => $combinedForecast,

            'serviceForecast'    => $serviceForecast,

            'forecastSummary'    => $forecastSummary,
            'forecastHorizon'    => $forecastHorizon,

            'prescriptiveReco'   => $prescriptiveReco,
            'weekdayRanking' => $ranking,
        ]);
    }
}
