<?php

namespace App\Http\Controllers;

use App\Models\TopUpRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminTopUpController extends Controller
{
    /**
     * Display a listing of pending top up requests.
     */
    public function index(Request $request)
    {
        $query = TopUpRequest::with('user')->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        return response()->json($query->get());
    }

    /**
     * Approve a top up request.
     */
    public function approve(Request $request, string $id)
    {
        $topUp = TopUpRequest::findOrFail($id);

        if ($topUp->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending'], 400);
        }

        DB::transaction(function () use ($topUp) {
            $topUp->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Credit User Wallet
            WalletService::creditTopUp($topUp->user, $topUp->amount, $topUp);
        });

        return response()->json(['message' => 'Top up approved successfully', 'data' => $topUp]);
    }

    /**
     * Reject a top up request.
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $topUp = TopUpRequest::findOrFail($id);

        if ($topUp->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending'], 400);
        }

        $topUp->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'approved_by' => Auth::id(), // Rejected by
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Top up rejected successfully', 'data' => $topUp]);
    }
}
