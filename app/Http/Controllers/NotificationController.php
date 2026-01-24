<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user.
     */
    public function index()
    {
        return response()->json(Auth::user()->unreadNotifications);
    }

    /**
     * Mark notification(s) as read.
     */
    public function markAsRead(Request $request)
    {
        $id = $request->input('id');

        if ($id) {
            Auth::user()->unreadNotifications->where('id', $id)->markAsRead();
        } else {
            Auth::user()->unreadNotifications->markAsRead();
        }

        return response()->json(['message' => 'Notifications marked as read']);
    }
}
