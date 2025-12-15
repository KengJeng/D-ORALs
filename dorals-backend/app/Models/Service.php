<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'service_id';

    protected $fillable = [
        'name',
        'duration',
    ];

    protected $casts = [
        'duration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function appointments()
    {
        return $this->belongsToMany(
            Appointment::class,
            'appointment_services',
            'service_id',
            'appointment_id'
        );
    }

    public function getDurationInHoursAttribute()
    {
        return round($this->duration / 60, 2);
    }
}