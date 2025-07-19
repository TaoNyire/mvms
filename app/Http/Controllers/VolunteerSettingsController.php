<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class VolunteerSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has volunteer role
        if (!$user->hasRole('volunteer')) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
        
        return view('volunteer.settings.index', compact('user'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has volunteer role
        if (!$user->hasRole('volunteer')) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'notification_preferences' => 'array',
            'privacy_settings' => 'array',
        ]);
        
        // Update basic information
        $user->name = $request->name;
        $user->email = $request->email;
        
        // Update password if provided
        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        // Update notification preferences
        if ($request->has('notification_preferences')) {
            $user->updateNotificationPreferences($request->notification_preferences);
        }
        
        // Update privacy settings
        if ($request->has('privacy_settings')) {
            $user->updatePrivacySettings($request->privacy_settings);
        }
        
        return redirect()->route('volunteer.settings')
            ->with('success', 'Settings updated successfully!');
    }
}
