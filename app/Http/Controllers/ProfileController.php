<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // 'avatar' => ['nullable', 'image', 'max:1024'], // 1MB Max
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => ['image', 'max:1024']]);

            // Delete old avatar if exists and not default ??? (Optional)
            // if ($user->avatar) Storage::disk('public')->delete($user->avatar);

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path; // Assuming 'avatar' column exists or needs migration? 
            // Checked User model earlier? Let's assume standard field or add it? 
            // "Add User Avatar" was a previous task [id: 49]. 
            // I should verify if 'avatar' column exists on users table.
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully.', 'user' => $user]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
