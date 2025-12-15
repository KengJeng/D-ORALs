<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';
    protected $primaryKey = 'log_id';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'log_date',
        'log_time',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function user()
    {
        return null;
    }

    public function scopeToday($query)
    {
        return $query->whereDate('log_date', today());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', 'like', "%{$action}%");
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('log_date', [$startDate, $endDate]);
    }
}