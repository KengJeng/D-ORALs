<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\QueueService;
use App\Services\NotificationService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueueController extends Controller
{
    protected $queueService;
    protected $notificationService;
    protected $auditLog;

    public function __construct(
        QueueService $queueService,
        NotificationService $notificationService,
        AuditLogService $auditLog
    ) {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
        $this->auditLog = $auditLog;
    }

    /**
     * Get next patient in queue
     */
    public function getNext(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $next = $this->queueService->getNextInQueue($date);

        if (!$next) {
            return response()->json([
                'message' => 'No patients in queue',
                'next' => null,
            ]);
        }

        return response()->json($next);
    }

    /**
     * Call next patient and update status
     */
    public function callNext(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $next = $this->queueService->getNextInQueue($date);

        if (!$next) {
            return response()->json([
                'message' => 'No patients in queue',
            ], 404);
        }

        DB::transaction(function () use ($next) {

            if ($next->status === 'Pending') {
                $next->update(['status' => 'Confirmed']);
            }

            Notification::create([
                'appointment_id' => $next->appointment_id,
                'queue_number'   => $next->queue_number,
                'message'        => "You're next. Please be ready in 15–20 minutes. Queue #: {$next->queue_number}",
                'is_sent'        => false,
                'created_at'     => now(),
            ]);

            $this->notificationService->sendTurnNotification($next, 0);
        });

        return response()->json([
            'message' => 'Patient called successfully',
            'patient' => $next->load(['patient', 'services']),
        ]);
    }

    /**
     * Get my queue position
     */
    public function myPosition(Request $request, $appointmentId)
    {
        try {
            $position = $this->queueService->getQueuePosition($appointmentId);

            return response()->json([
                'appointment_id' => $appointmentId,
                'position' => $position,
                'estimated_wait_minutes' => $position['people_ahead'] * 20,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Appointment not found',
            ], 404);
        }
    }

    public function todayQueue()
{
    $appointments = Appointment::with(['patient', 'services'])
        ->today() 
        ->whereIn('status', ['Pending', 'Confirmed'])
        ->orderBy('queue_number')
        ->get();

    return response()->json($appointments);
}


    /**
     * Get queue statistics for a specific date
     */
    public function stats(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $stats = $this->queueService->getQueueStats($date);

        return response()->json($stats);
    }

    public function markCompleted(Request $request, $appointmentId)
    {
        $appointment = Appointment::select([
            'appointment_id',
            'patient_id',
            'status',
            'updated_at'
        ])->findOrFail($appointmentId);

        $appointment->update(['status' => 'Completed']);

        $this->notificationService->sendStatusUpdate($appointment);
        $this->auditLog->log(null, "Appointment #{$appointmentId} marked as completed");

        return response()->json([
            'message' => 'Appointment marked as completed',
            'appointment' => $appointment,
        ]);
    }

    public function markNoShow(Request $request, $appointmentId)
    {
        $appointment = Appointment::select([
            'appointment_id',
            'patient_id',
            'status',
            'updated_at'
        ])->findOrFail($appointmentId);

        $appointment->update(['status' => 'No-show']);

        $this->notificationService->sendStatusUpdate($appointment);
        $this->auditLog->log(null, "Appointment #{$appointmentId} marked as no-show");

        return response()->json([
            'message' => 'Appointment marked as no-show',
            'appointment' => $appointment,
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'appointments' => 'required|array',
            'appointments.*.appointment_id' => 'required|exists:appointments,appointment_id',
            'appointments.*.queue_number' => 'required|integer|min:1',
        ]);

        $date = $request->input('date');
        $appointments = $request->input('appointments');

        DB::transaction(function () use ($date, $appointments) {
            foreach ($appointments as $item) {
                DB::table('appointments')
                    ->where('appointment_id', $item['appointment_id'])
                    ->whereDate('scheduled_date', $date)
                    ->update(['queue_number' => $item['queue_number']]);
            }
        });

        $this->auditLog->log(null, "Queue reordered for date: {$date}");

        return response()->json([
            'message' => 'Queue reordered successfully',
        ]);
    }

    public function history(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(7));
        $endDate = $request->input('end_date', Carbon::today());

        $history = Appointment::select([
            'appointment_id',
            'patient_id',
            'scheduled_date',
            'status',
            'queue_number',
            'created_at'
        ])
            ->with([
                'patient:patient_id,first_name,last_name,contact_no',
                'services:service_id,name,duration'
            ])
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('queue_number')
            ->paginate(50);

        return response()->json($history);
    }

    public function processNotifications(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $notifications = $this->notificationService->processQueueNotifications($date);

        return response()->json([
            'message' => 'Queue notifications processed',
            'notifications_sent' => count($notifications),
        ]);
    }
}
