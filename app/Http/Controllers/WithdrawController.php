<?php

namespace App\Http\Controllers;

use App\Models\WithdrawRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $requests = WithdrawRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'], // Min 10k IDR?
            'bank_name' => ['required', 'string'],
            'account_number' => ['required', 'string'],
            'account_holder_name' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // 1. Lock Funds via Wallet Service
        try {
            $this->walletService->requestWithdraw($user, $validated['amount']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        // 2. Create Request Record
        $withdrawRequest = WithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_holder_name' => $validated['account_holder_name'],
            'status' => 'pending',
        ]);

        return response()->json($withdrawRequest, 201);
    }
}
