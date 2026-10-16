<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of plans and payment methods for Superadmin
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('tenants')
            ->orderBy('sort_order')
            ->get();

        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();

        return Inertia::render('Admin/SubscriptionPlans/Index', [
            'plans' => $plans,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'string', 'in:bulan,tahun'],
            'description' => ['nullable', 'string', 'max:500'],
            'badge' => ['nullable', 'string', 'max:50'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_courses' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = Str::slug($validated['name']);
        // Ensure unique slug
        $count = SubscriptionPlan::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $validated['slug'] = $slug;
        $validated['features'] = array_values(array_filter($validated['features'] ?? []));

        SubscriptionPlan::create($validated);

        return redirect()->back()->with('success', "Paket {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'string', 'in:bulan,tahun'],
            'description' => ['nullable', 'string', 'max:500'],
            'badge' => ['nullable', 'string', 'max:50'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_courses' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['features'] = array_values(array_filter($validated['features'] ?? []));

        $plan->update($validated);

        return redirect()->back()->with('success', "Paket {$plan->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified plan
     */
    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->tenants()->count() > 0) {
            return redirect()->back()->with('error', "Paket {$plan->name} tidak dapat dihapus karena masih digunakan oleh tenant.");
        }

        $name = $plan->name;
        $plan->delete();

        return redirect()->back()->with('success', "Paket {$name} berhasil dihapus.");
    }

    /**
     * Update payment method setting (e-Wallet / QRIS)
     */
    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $paymentMethod->update($validated);

        return redirect()->back()->with('success', "Metode pembayaran {$paymentMethod->name} berhasil diperbarui.");
    }
}
