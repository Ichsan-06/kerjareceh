<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_slot_id',
        'approver_id',
        'approver_role',
        'decision',
        'reason',
    ];

    public function slot()
    {
        return $this->belongsTo(JobSlot::class, 'job_slot_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
