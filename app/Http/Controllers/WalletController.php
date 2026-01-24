<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get current user's wallet and transaction history.
     */
    public function index()
    {
        $user = auth()->user();

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'locked_balance' => 0]
        );

        $wallet->load(['transactions' => function ($query) {
            $query->latest()->take(20);
        }]);

        return response()->json($wallet);
    }
}
