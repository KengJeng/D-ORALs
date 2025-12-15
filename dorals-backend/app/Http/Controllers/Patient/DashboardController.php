<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Patient;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $patient = session('patient');

        if (! $patient) {
            return redirect()->route('patient.login');
        }

        $today = Carbon::today();

        $upcoming = Appointment::select([
            'appointment_id', 'patient_id', 'scheduled_date', 'status', 'queue_number'
        ])
        ->with(['services:service_id,name,duration'])
        ->where('patient_id', $patient->patient_id)
        ->whereDate('scheduled_date', '>=', $today)
        ->whereIn('status', ['Pending', 'Confirmed'])
        ->orderBy('scheduled_date')
        ->orderBy('queue_number')
        ->limit(10)
        ->get();

        $services = Cache::remember('public_services_list', 300, function () {
            return Service::select('service_id', 'name', 'duration')
                ->orderBy('name')
                ->get();
        });

        $upcomingCount = Appointment::where('patient_id', $patient->patient_id)
            ->whereDate('scheduled_date', '>=', $today)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->count();

        $lastAppointment = Appointment::select(['appointment_id', 'scheduled_date', 'status'])
            ->where('patient_id', $patient->patient_id)
            ->orderBy('scheduled_date', 'desc')
            ->first();

        return view('patient.dashboard', [
            'patient' => $patient,
            'upcoming' => $upcoming,
            'services' => $services,
            'upcoming_count' => $upcomingCount,
            'last_appointment' => $lastAppointment,
        ]);
    }

    /**
     * Display all patient appointments (paginated)
     */
    public function appointments(Request $request)
    {
        $patient = session('patient');

        if (! $patient) {
            return redirect()->route('patient.login');
        }

        $appointments = Appointment::select([
            'appointment_id', 'patient_id', 'scheduled_date', 'status', 'queue_number'
        ])
        ->with(['services:service_id,name,duration'])
        ->where('patient_id', $patient->patient_id)
        ->orderBy('scheduled_date', 'desc')
        ->paginate(10);

        return view('patient.appointments', [
            'patient' => $patient,
            'appointments' => $appointments,
        ]);
    }
}
