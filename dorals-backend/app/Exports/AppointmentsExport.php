<?php

namespace App\Exports;

use App\Models\Appointment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppointmentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $from;
    protected $to;
    protected $status;

    public function __construct($from = null, $to = null, $status = null)
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Appointment::with('patient');

        if ($this->from) {
            $query->whereDate('scheduled_date', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('scheduled_date', '<=', $this->to);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('scheduled_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Appointment ID',
            'Patient Name',
            'Scheduled Date',
            'Status',
            'Queue Number',
            'Created At',
        ];
    }

    public function map($appointment): array
    {
        return [
            $appointment->appointment_id,
            optional($appointment->patient)->first_name . ' ' . optional($appointment->patient)->last_name,
            $appointment->scheduled_date,
            $appointment->status,
            $appointment->queue_number,
            $appointment->created_at,
        ];
    }
}
