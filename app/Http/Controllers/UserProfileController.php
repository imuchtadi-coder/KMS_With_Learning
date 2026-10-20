<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user()->load([
            'tenant',
            'roles',
            'enrollments.course',
            'certificates',
            'userSkills.skill',
            'userSkills.endorsements.endorser:id,name',
        ]);

        $skillCatalog = \App\Models\Skill::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $user->tenant_id)->orWhereNull('tenant_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'description']);

        return Inertia::render('Profile/Show', compact('user', 'skillCatalog'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user()->load('tenant');

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'department' => $user->department,
                'address' => $user->address,
                'tenant' => $user->tenant,
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'birthday' => 'nullable|date|before:today',
            'department' => 'nullable|string|max:100',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
            'birthday' => $request->birthday,
            'department' => $request->department,
        ]);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password updated successfully!');
    }
}
