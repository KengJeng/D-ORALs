<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Send notification when appointment status changes
     */
    public function sendStatusUpdate(Appointment $appointment): Notification
    {
        $messages = [
            'Confirmed' => "Your appointment has been confirmed. Queue number: #{$appointment->queue_number}",
            'Completed' => "Thank you for visiting! Your appointment has been completed.",
            'Canceled' => "Your appointment has been canceled. Please contact us if you have questions.",
            'No-show' => "You missed your appointment. Please reschedule at your earliest convenience.",
        ];

        $message = $messages[$appointment->status] ?? "Appointment status updated to: {$appointment->status}";

        return $this->createNotification($appointment, $message);
    }

    /**
     * Send notification when it's almost patient's turn
     */
    public function sendTurnNotification(Appointment $appointment, int $peopleAhead): Notification
    {
        $message = $peopleAhead > 0
            ? "You're next! There are {$peopleAhead} patient(s) ahead of you. Please be ready."
            : "It's your turn now! Please proceed to the counter. Queue #: {$appointment->queue_number}";

        return $this->createNotification($appointment, $message);
    }

    /**
     * Send reminder notification
     */
    public function sendReminder(Appointment $appointment): Notification
    {
        $date = Carbon::parse($appointment->scheduled_date)->format('F d, Y');
        $message = "Reminder: You have an appointment scheduled for {$date}. Queue #: {$appointment->queue_number}";

        return $this->createNotification($appointment, $message);
    }

    /**
     * Create notification record
     */
    private function createNotification(Appointment $appointment, string $message): Notification
    {
        return Notification::create([
            'appointment_id' => $appointment->appointment_id,
            'queue_number' => $appointment->queue_number,
            'message' => $message,
            'is_sent' => false,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Get unsent notifications
     */
    public function getUnsentNotifications()
    {
        return Notification::with('appointment.patient')
            ->unsent()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent($notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->markAsSent();
            return true;
        }

        return false;
    }

    /**
     * Get patient notifications
     */
    public function getPatientNotifications($patientId, int $limit = 20)
    {
        return Notification::whereHas('appointment', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Send batch notifications for upcoming appointments
     */
    public function sendUpcomingAppointmentReminders($date = null)
    {
        $date = $date ?? Carbon::tomorrow()->toDateString();

        $appointments = Appointment::with('patient')
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->get();

        $notifications = [];
        foreach ($appointments as $appointment) {
            $notifications[] = $this->sendReminder($appointment);
        }

        return $notifications;
    }

    /**
     * Process queue notifications (notify when it's almost their turn)
     */
    public function processQueueNotifications($date = null)
    {
        $date = $date ?? Carbon::today()->toDateString();

        $appointments = Appointment::with('patient')
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->orderBy('queue_number')
            ->get();

        $notifications = [];
        $currentPosition = 0;

        foreach ($appointments as $index => $appointment) {
            $peopleAhead = $index;

            if ($peopleAhead <= 2) {
                $existingNotification = Notification::where('appointment_id', $appointment->appointment_id)
                    ->where('message', 'like', '%your turn%')
                    ->where('created_at', '>=', Carbon::now()->subMinutes(30))
                    ->first();

                if (!$existingNotification) {
                    $notifications[] = $this->sendTurnNotification($appointment, $peopleAhead);
                }
            }
        }

        return $notifications;
    }
}