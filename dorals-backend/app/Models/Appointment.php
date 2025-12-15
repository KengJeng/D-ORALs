<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'patient_id',
        'scheduled_date',
        'status',
        'queue_number',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    protected $appends = ['google_calendar_url'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'admin_id');
    }

    public function updatedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'updated_by', 'admin_id');
    }

    public function appointmentsCreated()
    {
        return $this->hasMany(Appointment::class, 'created_by', 'admin_id');
    }

    public function appointmentsUpdated()
    {
        return $this->hasMany(Appointment::class, 'updated_by', 'admin_id');
    }

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'appointment_services',
            'appointment_id',
            'service_id'
        );
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'appointment_id', 'appointment_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'Confirmed');
    }

    /**
     * Google Calendar "Add event" URL
     */
    public function getGoogleCalendarUrlAttribute(): string
    {
        $title = 'Dental Appointment - ' . trim(
            optional($this->patient)->first_name . ' ' . optional($this->patient)->last_name
        );

        $start = Carbon::parse($this->scheduled_date)->format('Ymd');
        $end   = Carbon::parse($this->scheduled_date)->addDay()->format('Ymd');

        $details  = 'Appointment scheduled via D-ORAL System.';
        $location = 'Dental Clinic';

        $query = http_build_query([
            'action'   => 'TEMPLATE',
            'text'     => $title,
            'dates'    => "{$start}/{$end}",
            'details'  => $details,
            'location' => $location,
            'sf'       => 'true',
            'output'   => 'xml',
        ]);

        return 'https://calendar.google.com/calendar/render?' . $query;
    }
}
