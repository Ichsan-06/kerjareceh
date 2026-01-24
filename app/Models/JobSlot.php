<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'worker_id',
        'reward_amount',
        'status',
        'reserved_at',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'reserved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function submission()
    {
        return $this->hasOne(JobSubmission::class, 'job_slot_id');
    }
}
