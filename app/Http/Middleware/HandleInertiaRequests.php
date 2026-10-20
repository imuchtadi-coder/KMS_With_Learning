<?php

namespace App\Http\Middleware;

use App\Models\TenantBranding;
use App\Models\WhatsappCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /*
         * ============================================================
         * MASTER WHATSAPP CUSTOMER SERVICE
         * ============================================================
         */
        $whatsappCustomerService = WhatsappCustomerService::query()
            ->where('is_active', true)
            ->first();

        /*
         * ============================================================
         * MASTER TENANT BRANDING
         * ============================================================
         */
        $user = $request->user();

        $branding = null;
        if ($user && $user->tenant?->branding) {
            $branding = $user->tenant->branding;
        } else {
            $branding = TenantBranding::whereNotNull('agency_name')->first();
        }

        $tenantBranding = [
            'agency_name' => $branding?->agency_name ?: 'KMS Learning',
            'tagline' => $branding?->tagline ?: 'Belajar asik dengan Digital',
            'logo_url' => ($branding && $branding->logo_path)
                ? Storage::disk('public')->url($branding->logo_path)
                : null,
        ];

        return [
            ...parent::share($request),

            /*
             * ========================================================
             * AUTH
             * ========================================================
             *
             * Jangan mengubah pola ini karena digunakan oleh:
             * - Navigation
             * - User Profile
             * - Role checking
             * - Logout
             * - Komponen lain
             */
            'auth' => fn () => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'tenant_id' => $request->user()->tenant_id,
                    'roles' => $request->user()->roles->pluck('name'),
                ] : null,
            ],

            'languageScope' => $user && !$user->hasRole('superadmin') && $user->tenant_id
                ? 'tenant:' . $user->tenant_id
                : 'global',

            /*
             * ========================================================
             * TENANT BRANDING
             * ========================================================
             *
             * Super Admin => null
             *
             * Tenant Admin / Instructor / Student =>
             * branding tenant masing-masing.
             */
            'tenantBranding' => $tenantBranding,

            /*
             * ========================================================
             * ERRORS
             * ========================================================
             */
            'errors' => fn () => (
                $errors = $request->session()->get('errors')
            ) instanceof \Illuminate\Support\ViewErrorBag
                ? $errors->getBag('default')->toArray()
                : (array) ($errors ?? []),

            /*
             * ========================================================
             * FLASH
             * ========================================================
             */
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),

                'import_errors' => fn () => $request->session()->get(
                    'import_errors',
                    []
                ),
            ],

            /*
             * ========================================================
             * NOTIFICATIONS
             * ========================================================
             */
            'notifications' => fn () => $request->user() ? [
                'unreadCount' => $request->user()
                    ->unreadNotifications
                    ->count(),

                'items' => $request->user()
                    ->notifications()
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($n) => [
                        'id' => $n->id,

                        'title' => strip_tags(
                            str_replace(
                                ['<strong>', '</strong>'],
                                '',
                                $n->data['message'] ?? 'Notification'
                            )
                        ),

                        'subtitle' => $n->data['course_title']
                            ?? $n->data['quiz_title']
                            ?? $n->data['assignment_title']
                            ?? '',

                        'time' => $n->created_at->diffForHumans(),

                        'isSeen' => $n->read_at !== null,

                        'action_url' => $n->data['action_url'] ?? '#',
                    ]),
            ] : null,

            /*
             * ========================================================
             * MASTER CUSTOMER SERVICE WHATSAPP
             * ========================================================
             */
            'whatsappCustomerService' => $whatsappCustomerService
                ? [
                    'name' => $whatsappCustomerService->name,
                    'phone_number' => $whatsappCustomerService->phone_number,
                ]
                : null,
        ];
    }
}
