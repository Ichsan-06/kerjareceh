<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Get the leaderboard based on user earnings (fees).
     */
    public function index()
    {
        // Query Logic:
        // 1. Filter WalletTransactions for type 'fee' (earnings).
        // 2. Join with Wallets table to get user_id.
        // 3. Join with Users table to get user name/avatar.
        // 4. Group by User.
        // 5. Sum amount.
        // 6. Order by Total Amount DESC.

        $leaderboard = WalletTransaction::select(
            'users.id',
            'users.name',
            'users.email', // Maybe mask this or use avatar
            DB::raw('SUM(wallet_transactions.amount) as total_earnings')
        )
            ->join('wallets', 'wallet_transactions.wallet_id', '=', 'wallets.id')
            ->join('users', 'wallets.user_id', '=', 'users.id')
            ->where('wallet_transactions.type', 'fee')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_earnings')
            ->limit(10)
            ->get();

        return response()->json($leaderboard);
    }
}
