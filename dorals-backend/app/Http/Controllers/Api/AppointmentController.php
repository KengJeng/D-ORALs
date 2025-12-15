<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Appointment;
use App\Services\NotificationService;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    protected $queueService;

    protected $notificationService;

    public function __construct(
        QueueService $queueService,
        NotificationService $notificationService
    ) {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
    }

    /**
     * Admin creates an appointment (e.g., walk-in patient).
     */
    public function adminStore(Request $request)
    {
        $user = $request->user();
        if (! ($user instanceof Admin)) {
            abort(403, 'Admin access only.');
        }
        $adminId = $user->admin_id;

        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,service_id',
        ]);

        $appointment = DB::transaction(function () use ($request, $adminId) {

            // 1. Admin creates appointment
            $appointment = Appointment::create([
                'patient_id' => $request->patient_id,
                'scheduled_date' => $request->scheduled_date,
                'status' => 'Pending',
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ]);

            // 2. Attach services
            $appointment->services()->attach($request->service_ids);

            // 3. Assign queue number
            $queueNumber = $this->queueService->assignQueue($appointment);
            $appointment->queue_number = $queueNumber;
            $appointment->save();

            return $appointment->load(['patient', 'services']);
        });

        return response()->json([
            'message' => 'Walk-in appointment created successfully',
            'appointment' => $appointment,
        ], 201);
    }

    public function index(Request $request)
    {
        // OPTIMIZATION: Use indexes on date/status, select necessary columns only
        $query = Appointment::with([
            'patient:patient_id,first_name,middle_name,last_name,email,contact_no',
            'services:service_id,name,duration',
        ])
            ->select([
                'appointment_id',
                'patient_id',
                'scheduled_date',
                'status',
                'queue_number',
                'created_at',
            ]);

        // Use indexes for filtering
        if ($request->has('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('queue_number')
            ->paginate(20);

        return response()->json($appointments);
    }

    /**
     * Patient creates an appointment
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,service_id',
        ]);

        $appointment = DB::transaction(function () use ($request) {

            // 1. Create appointment
            $appointment = Appointment::create([
                'patient_id' => $request->patient_id,
                'scheduled_date' => $request->scheduled_date,
                'status' => 'Pending',
                'created_by' => null,
                'updated_by' => null,
            ]);

            // 2. Attach services
            $appointment->services()->attach($request->service_ids);

            // 3. Assign queue number
            $queueNumber = $this->queueService->assignQueue($appointment);
            $appointment->queue_number = $queueNumber;
            $appointment->save();

            // 4. Return fully loaded appointment from inside the transaction
            return $appointment->load(['patient', 'services']);
        });

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment,
        ], 201);
    }

    public function show($id)
    {
        // OPTIMIZATION: Selective column loading for related models
        $appointment = Appointment::select([
            'appointment_id',
            'patient_id',
            'scheduled_date',
            'status',
            'queue_number',
            'created_at',
            'updated_at',
        ])
            ->with([
                'patient:patient_id,first_name,middle_name,last_name,email,contact_no,sex',
                'services:service_id,name,duration',
                'notifications:notification_id,appointment_id,message,is_sent',
            ])
            ->findOrFail($id);

        return response()->json($appointment);
    }

    public function update(Request $request, $id)
{
    $user = $request->user();


    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Admin access only.'], 403);  
    }

    $appointment = Appointment::findOrFail($id);

    $request->validate([
        'scheduled_date' => 'sometimes|date|after_or_equal:today',
        'status' => 'sometimes|in:Pending,Confirmed,Completed,Canceled,No-show',
        'service_ids' => 'sometimes|array|min:1',
        'service_ids.*' => 'exists:services,service_id',
    ]);

    if ($request->has('scheduled_date')) {
        $appointment->scheduled_date = $request->scheduled_date;

        $queueNumber = $this->queueService->assignQueue($appointment);
        $appointment->queue_number = $queueNumber;
    }

    if ($request->has('status')) {
        $oldStatus = $appointment->status;
        $appointment->status = $request->status;

        if ($oldStatus !== $request->status) {
            $this->notificationService->sendStatusUpdate($appointment);
        }
    }

    $appointment->save();

    if ($request->has('service_ids')) {
        $appointment->services()->sync($request->service_ids);
    }
    return response()->json([
        'message' => 'Appointment updated successfully',
        'appointment' => $appointment->load(['patient', 'services']),
    ]);
}

    public function destroy(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $patientId = $appointment->patient_id;

        $user = $request->user();
        if (! ($user instanceof Admin)) {
            abort(403, 'Admin access only.');
        }
        $adminId = $user->admin_id;

        DB::transaction(function () use ($appointment) {
            $appointment->delete();
        });

        return response()->json([
            'message' => 'Appointment deleted successfully',
        ]);
    }

    public function myAppointments(Request $request)
    {
        $patient = $request->user();

        // OPTIMIZATION: Select specific columns and use indexes
        $appointments = Appointment::select([
            'appointment_id',
            'patient_id',
            'scheduled_date',
            'status',
            'queue_number',
            'created_at',
        ])
            ->with('services:service_id,name,duration')
            ->where('patient_id', $patient->patient_id)
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        return response()->json($appointments);
    }

    public function todayQueue()
    {
        $today = Carbon::today()->toDateString();

        $appointments = Appointment::select([
            'appointment_id',
            'patient_id',
            'scheduled_date',
            'status',
            'queue_number',
            'created_at',
        ])
            ->with([
                'patient:patient_id,first_name,last_name,contact_no',
                'services:service_id,name,duration',
            ])
            ->whereDate('scheduled_date', $today)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->orderBy('queue_number')
            ->get();

        return response()->json($appointments);
    }
}
