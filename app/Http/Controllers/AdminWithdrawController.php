<?php

namespace App\Http\Controllers;

use App\Models\WithdrawRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminWithdrawController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $requests = WithdrawRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($requests);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'proof' => ['required', 'image', 'max:2048']
        ]);

        $withdrawRequest = WithdrawRequest::findOrFail($id);

        if ($withdrawRequest->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending'], 400);
        }

        $path = $request->file('proof')->store('withdraw_proofs', 'public');

        try {
            $this->walletService->approveWithdraw($withdrawRequest);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        $withdrawRequest->update([
            'status' => 'approved',
            'proof_path' => $path,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json($withdrawRequest);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => ['required', 'string']
        ]);

        $withdrawRequest = WithdrawRequest::findOrFail($id);

        if ($withdrawRequest->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending'], 400);
        }

        try {
            $this->walletService->rejectWithdraw($withdrawRequest);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        $withdrawRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'approved_by' => Auth::id(), // Acted by
            'approved_at' => now(), // Acted at
        ]);

        return response()->json($withdrawRequest);
    }
}
