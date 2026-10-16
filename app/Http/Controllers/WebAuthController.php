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
            $user = Auth::user();

            // Check if tenant is pending or rejected
            if ($user->tenant_id && $user->tenant) {
                if ($user->tenant->status === 'pending') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Pendaftaran institusi Anda sedang menunggu persetujuan dari Super Admin.',
                    ])->onlyInput('email');
                }

                if ($user->tenant->status === 'rejected') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Pendaftaran institusi Anda telah ditolak oleh Super Admin. Silakan hubungi Admin/CS.',
                    ])->onlyInput('email');
                }
            }

            $request->session()->regenerate();

            // Redirect superadmin to admin dashboard
            if ($user->hasRole('superadmin')) {
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
        return inertia('Auth/Register');
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

        // Default tenant for public learners is tenant-a (Universitas Teknologi)
        $defaultTenantId = \App\Models\Tenant::where('status', 'active')->first()?->id ?? 'tenant-a';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $defaultTenantId,
            'email_verified_at' => now(),
        ]);

        // Assign student role
        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard'))->with('success', 'Akun berhasil dibuat! Selamat datang di KITsuluh.');
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
