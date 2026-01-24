<?php

namespace App\Http\Controllers;

use App\Models\TopUpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TopUpController extends Controller
{
    /**
     * Display a listing of user's top up requests.
     */
    public function index()
    {
        $requests = TopUpRequest::where('user_id', Auth::id())
            ->latest()
            ->get();
        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_method' => 'required|string|in:bank_transfer,e_wallet',
            'proof' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        // Calculate admin fee (can be dynamic later)
        $adminFee = 0;
        $totalAmount = $validated['amount'] + $adminFee;

        // Handle File Upload
        $path = $request->file('proof')->store('proofs', 'public');

        $topUp = TopUpRequest::create([
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'admin_fee' => $adminFee,
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'proof_path' => $path,
            'status' => 'pending',
        ]);

        // Notify Admins
        $admins = \App\Models\User::permission('approve topup')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewTopUpRequest($topUp, Auth::user()));

        return response()->json($topUp, 201);
    }
}
