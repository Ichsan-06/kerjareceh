<?php

namespace App\Http\Controllers;

use App\Models\JobSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LandingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index()
    {
        // Cache statistics for 5 minutes to reduce database load
        $stats = Cache::remember('landing_stats', 300, function () {

            $totalTaskers = User::role('member')->count();

            // If User model doesn't have role scope yet (spatie), use whereHas
            if ($totalTaskers === 0) {
                $totalTaskers = User::whereHas('roles', function ($q) {
                    $q->where('name', 'member');
                })->count();
            }

            // Estimate, or use real count if you have users
            if ($totalTaskers < 10) {
                $totalTaskers = 1500; // Mockup start for 'active' feel if DB is empty
            }

            $completedTasks = JobSlot::where('status', 'approved')->count();
            if ($completedTasks < 10) {
                $completedTasks = 5000; // Mockup start
            }

            $totalPayout = JobSlot::where('status', 'approved')->sum('reward_amount');
            if ($totalPayout < 100000) {
                $totalPayout = 500000000; // Mockup start (500jt)
            }

            return [
                'total_taskers' => $totalTaskers,
                'total_tasks_completed' => $completedTasks,
                'total_payout' => $totalPayout,
            ];
        });

        return response()->json($stats);
    }
}
