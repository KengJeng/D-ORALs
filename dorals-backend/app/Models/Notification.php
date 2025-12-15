<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_id';
    
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'queue_number',
        'message',
        'is_sent',
        'created_at',
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    public function markAsSent()
    {
        $this->update(['is_sent' => true]);
    }
}