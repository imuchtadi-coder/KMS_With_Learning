<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LandingController extends Controller
{
    /**
     * Display the landing page
     */
    public function index(Request $request)
    {
        $stats = [
            'total_courses' => Course::where('published', true)->count(),
            'total_students' => User::role('student')->count(),
            'total_tenants' => Tenant::where('status', 'active')->count(),
        ];

        $cs = WhatsappCustomerService::where('is_active', true)->first();
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Landing/Index', [
            'stats' => $stats,
            'plans' => $plans,
            'cs' => $cs ? [
                'name' => $cs->name,
                'phone_number' => $cs->phone_number,
            ] : null,
        ]);
    }
}
