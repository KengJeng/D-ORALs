<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class QueueService
{
    /**
     * Assign queue number to appointment
     */
    public function assignQueue(Appointment $appointment): int
    {
        $date = $appointment->scheduled_date;
        
        $lastQueue = Appointment::whereDate('scheduled_date', $date)
            ->max('queue_number');

        return ($lastQueue ?? 0) + 1;
    }

    /**
     * Get current queue position for a patient
     */
    public function getQueuePosition($appointmentId): array
    {
        $appointment = Appointment::findOrFail($appointmentId);
        
        $totalAhead = Appointment::whereDate('scheduled_date', $appointment->scheduled_date)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->where('queue_number', '<', $appointment->queue_number)
            ->count();

        $totalToday = Appointment::whereDate('scheduled_date', $appointment->scheduled_date)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->count();

        return [
            'queue_number' => $appointment->queue_number,
            'position' => $totalAhead + 1,
            'total_in_queue' => $totalToday,
            'people_ahead' => $totalAhead,
        ];
    }

    /**
     * Get next patient in queue
     */
    public function getNextInQueue($date = null): ?Appointment
    {
        $date = $date ?? now()->toDateString();

        return Appointment::with(['patient', 'services'])
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->orderBy('queue_number')
            ->first();
    }

    /**
     * Mark current as served and get next
     */
    public function markServedAndGetNext($appointmentId): ?Appointment
    {
        $current = Appointment::findOrFail($appointmentId);
        $current->update(['status' => 'Completed']);

        return $this->getNextInQueue($current->scheduled_date);
    }

    /**
     * Get queue statistics for a date
     */
    public function getQueueStats($date = null): array
    {
        $date = $date ?? now()->toDateString();

        $total = Appointment::whereDate('scheduled_date', $date)->count();
        $pending = Appointment::whereDate('scheduled_date', $date)->where('status', 'Pending')->count();
        $confirmed = Appointment::whereDate('scheduled_date', $date)->where('status', 'Confirmed')->count();
        $completed = Appointment::whereDate('scheduled_date', $date)->where('status', 'Completed')->count();
        $canceled = Appointment::whereDate('scheduled_date', $date)->where('status', 'Canceled')->count();
        $noShow = Appointment::whereDate('scheduled_date', $date)->where('status', 'No-show')->count();

        return [
            'date' => $date,
            'total' => $total,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'completed' => $completed,
            'canceled' => $canceled,
            'no_show' => $noShow,
            'active' => $pending + $confirmed,
        ];
    }
}