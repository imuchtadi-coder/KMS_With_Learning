<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantBranding;
use App\Models\User;
use App\Models\WhatsappCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class TenantRegistrationController extends Controller
{
    /**
     * Show registration form for tenant
     */
    public function create(Request $request)
    {
        $cs = WhatsappCustomerService::where('is_active', true)->first();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Auth/RegisterTenant', [
            'cs' => $cs ? [
                'name' => $cs->name,
                'phone_number' => $cs->phone_number,
            ] : null,
            'plans' => $plans,
            'paymentMethods' => $paymentMethods,
            'selectedPlanSlug' => $request->query('plan'),
        ]);
    }

    /**
     * Handle tenant registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'institution_name' => ['required', 'string', 'max:255'],
            'tenant_slug' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9-]+$/i',
                'unique:tenants,id',
            ],
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_proof' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'plan_id.required' => 'Silakan tentukan paket langganan institusi Anda.',
            'tenant_slug.regex' => 'ID Subdomain hanya boleh menggunakan huruf kecil, angka, dan tanda hubung (-).',
            'tenant_slug.unique' => 'ID Subdomain ini sudah digunakan oleh institusi lain.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'payment_method.required' => 'Pilih salah satu metode pembayaran e-Wallet.',
            'payment_proof.required' => 'Silakan unggah bukti transaksi pembayaran e-Wallet.',
            'payment_proof.image' => 'File bukti pembayaran harus berupa gambar (JPG, PNG, WEBP).',
            'payment_proof.max' => 'Ukuran file bukti pembayaran tidak boleh melebihi 5MB.',
        ]);

        $tenantSlug = strtolower($validated['tenant_slug']);
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Upload payment proof to storage/app/public/payment_proofs
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $fileName = 'proof_' . $tenantSlug . '_' . time() . '.' . $file->getClientOriginalExtension();
            $proofPath = $file->storeAs('payment_proofs', $fileName, 'public');
        }

        DB::transaction(function () use ($validated, $tenantSlug, $plan, $proofPath) {
            // 1. Create Tenant with 'pending' status & subscription/payment info
            $tenant = Tenant::create([
                'id' => $tenantSlug,
                'name' => $validated['institution_name'],
                'status' => 'pending',
                'contact_person' => $validated['admin_name'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
                'plan_id' => $plan->id,
                'payment_method' => $validated['payment_method'],
                'payment_proof' => $proofPath,
                'payment_status' => 'pending',
                'amount_paid' => $plan->price,
            ]);

            // 2. Create Domain
            $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'kitsuluh.test';
            Domain::create([
                'domain' => "{$tenantSlug}.{$host}",
                'tenant_id' => $tenant->id,
            ]);

            // 3. Create Tenant Branding
            TenantBranding::create([
                'tenant_id' => $tenant->id,
                'agency_name' => $validated['institution_name'],
                'tagline' => 'Portal Pembelajaran Resmi',
            ]);

            // 4. Create Tenant Admin User
            $admin = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'tenant_id' => $tenant->id,
                'email_verified_at' => now(),
            ]);

            $admin->assignRole('tenant_admin');
        });

        return redirect()->route('register-tenant')->with('registration_success', [
            'institution_name' => $validated['institution_name'],
            'tenant_slug' => $tenantSlug,
            'email' => $validated['email'],
            'admin_name' => $validated['admin_name'],
            'plan_name' => $plan->name,
            'amount_paid' => $plan->price,
            'payment_method' => strtoupper($validated['payment_method']),
        ]);
    }
}
