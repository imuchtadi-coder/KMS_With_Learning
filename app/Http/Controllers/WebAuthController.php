<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class WebAuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return inertia('Auth/Login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect superadmin to admin dashboard
            if (Auth::user()->hasRole('superadmin')) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, Admin!');
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show register form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign student role
        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard'))->with('success', 'Account created successfully!');
    }

    /**
     * Show dashboard - redirect based on role
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Redirect based on role
        if($user->hasRole('superadmin')) {
            return redirect()->route('admin.dashboard');
        } elseif($user->hasRole('tenant_admin')) {
            return redirect()->route('admin.dashboard');
        } elseif($user->hasRole('instructor')) {
            return redirect()->route('instructor.dashboard');
        } elseif($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        // Default student view
        return view('dashboard', [
            'user' => $user,
            'enrollments' => $user->enrollments()->with('course')->get(),
            'courses' => \App\Models\Course::where('published', true)->get(),
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'))->with('success', 'You have been logged out.');
    }
}
