<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model is in App\Models namespace

class Feedback extends Model
{
    protected $fillable = ['user_id', 'message', 'type', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
