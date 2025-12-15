<?php

namespace App\Http\Controllers;

use App\Exports\AppointmentsExport;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Show the appointments report page
     */
    public function appointments(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');   
        $status = $request->query('status'); 

        $query = Appointment::with('patient');

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
     * Export the appointments report as Excel.
     */
    public function exportAppointments(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $request->query('status');

        return Excel::download(
            new AppointmentsExport($from, $to, $status),
            'appointments_report_'.now()->format('Ymd_His').'.xlsx'
        );
    }
}
