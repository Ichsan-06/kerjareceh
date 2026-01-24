<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'gig_jobs';

    protected $fillable = [
        'provider_id',
        'job_type_id',
        'title',
        'description',
        'reward_per_worker',
        'total_budget',
        'total_slot',
        'slot_taken',
        'status',
        'approval_deadline_minutes',
        'submit_deadline_minutes',
        'start_at',
        'end_at'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'reward_per_worker' => 'decimal:2',
        'total_budget' => 'decimal:2',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function slots()
    {
        return $this->hasMany(JobSlot::class, 'job_id');
    }
}
