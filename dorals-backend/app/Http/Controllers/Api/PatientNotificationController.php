<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class PatientNotificationController extends Controller
{
    public function index(Request $request)
    {
        $patient = $request->user(); // must be the authenticated Patient

        // Return latest notifications for that patient's appointments
        $rows = Notification::query()
            ->whereHas('appointment', function ($q) use ($patient) {
                $q->where('patient_id', $patient->patient_id);
            })
            ->orderByDesc('notification_id')
            ->limit(30)
            ->get([
                'notification_id',
                'appointment_id',
                'queue_number',
                'message',
                'is_sent',
                'created_at',
            ]);

        return response()->json([
            'data' => $rows
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $patient = $request->user();

        $notif = Notification::where('notification_id', $id)
            ->whereHas('appointment', function ($q) use ($patient) {
                $q->where('patient_id', $patient->patient_id);
            })
            ->firstOrFail();

        $notif->update(['is_sent' => true]);

        return response()->json(['message' => 'Marked as read']);
    }
}
