<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_slot_id',
        'worker_id',
        'submission_data',
        'screenshot_path',
        'submitted_at',
    ];

    protected $casts = [
        'submission_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function slot()
    {
        return $this->belongsTo(JobSlot::class, 'job_slot_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}
