<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\JobSlot;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Statistics
        $stats = [
            'balance' => $wallet ? $wallet->balance : 0,

            // Total Earned: Sum of 'fee' transactions (which are earnings for users)
            'total_earned' => $wallet ? $wallet->transactions()->where('type', 'fee')->sum('amount') : 0,

            // Total Spent: Sum of 'lock' or 'payout' (roughly) for Providers posting jobs
            // A better metric for 'Total Spent' might be sum of budgets of posted jobs
            'total_spent' => Job::where('provider_id', $user->id)->sum('total_budget'),

            // Jobs Completed: Slots where status is 'approved'
            'jobs_completed_count' => JobSlot::where('worker_id', $user->id)->where('status', 'approved')->count(),

            // Jobs Posted: Active jobs
            'jobs_posted_count' => Job::where('provider_id', $user->id)->where('status', 'active')->count(),

            // Jobs In Progress (Taken but not approved yet)
            'jobs_taken_count' => JobSlot::where('worker_id', $user->id)->whereIn('status', ['reserved', 'submitted'])->count(),
        ];

        // Recent Activity (Transactions)
        $recentActivity = $wallet ? $wallet->transactions()->latest()->limit(5)->get() : [];

        return response()->json([
            'stats' => $stats,
            'recent_activity' => $recentActivity
        ]);
    }
}
