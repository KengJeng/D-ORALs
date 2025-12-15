<?php

namespace App\Http\Controllers;

use App\Exports\AppointmentsExport;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Show the appointments report page (web view).
     */
    public function appointments(Request $request)
    {
        // Optional filters from query string
        $from = $request->query('from');   // e.g. 2025-12-01
        $to = $request->query('to');     // e.g. 2025-12-31
        $status = $request->query('status'); // Completed, Canceled, etc.

        $query = Appointment::with('patient'); // adjust relationships as needed

        if ($from) {
            $query->whereDate('scheduled_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('scheduled_date', '<=', $to);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query
            ->orderBy('scheduled_date', 'asc')
            ->paginate(20)
            ->appends($request->query());

        // You can also compute summary stats here if needed (totals, completion rate, etc.)

        return view('reports.appointments', [
            'appointments' => $appointments,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Export the appointments report as CSV.
     */
    public function exportAppointments(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');

        // Excel or CSV implementation here
        return Excel::download(
            new AppointmentsExport($from, $to, $status),
            'appointments_report_'.now()->format('Ymd_His').'.xlsx'
        );
    }
}
