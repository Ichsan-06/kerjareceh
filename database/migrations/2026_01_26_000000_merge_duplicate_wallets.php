<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicates = Wallet::select('user_id')
            ->selectRaw('count(*) as count')
            ->groupBy('user_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $userId = $dup->user_id;

            // Get all wallets for this user, ordered by ID (assuming lower ID is the 'main' or 'original' one)
            $wallets = Wallet::where('user_id', $userId)->orderBy('id')->get();

            if ($wallets->count() < 2) continue;

            $mainWallet = $wallets->first();

            // Iterate over the rest
            foreach ($wallets->slice(1) as $modWallet) {
                // Merge Balance
                $mainWallet->balance += $modWallet->balance;
                $mainWallet->locked_balance += $modWallet->locked_balance;

                // Move Transactions
                WalletTransaction::where('wallet_id', $modWallet->id)
                    ->update(['wallet_id' => $mainWallet->id]);

                // Move WalletLocks (if any table for that)
                // Checking WalletService, there is WalletLock model.
                // Assuming wallet_locks table exists.
                if (Schema::hasTable('wallet_locks')) {
                    DB::table('wallet_locks')->where('wallet_id', $modWallet->id)
                        ->update(['wallet_id' => $mainWallet->id]);
                }

                // Delete the duplicate wallet
                $modWallet->delete();
            }

            $mainWallet->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily reverse a merge without transaction logs
    }
};
