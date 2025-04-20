<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'last_maintenance',
        'next_maintenance',
        'total_jobs',
        'completed_jobs',
        'pending_jobs',
        'failed_jobs',
    ];

    protected $casts = [
        'last_maintenance' => 'datetime',
        'next_maintenance' => 'datetime',
        'total_jobs' => 'integer',
        'completed_jobs' => 'integer',
        'pending_jobs' => 'integer',
        'failed_jobs' => 'integer',
    ];

    public function isOperational()
    {
        return $this->status === 'operational';
    }

    public function isInMaintenance()
    {
        return $this->status === 'maintenance';
    }

    public function isOffline()
    {
        return $this->status === 'offline';
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'operational':
                return '<span class="badge bg-success">Operational</span>';
            case 'maintenance':
                return '<span class="badge bg-warning">Maintenance</span>';
            case 'offline':
                return '<span class="badge bg-danger">Offline</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }
} 