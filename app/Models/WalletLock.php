<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'job_id',
        'job_slot_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function slot()
    {
        return $this->belongsTo(JobSlot::class, 'job_slot_id');
    }
}
