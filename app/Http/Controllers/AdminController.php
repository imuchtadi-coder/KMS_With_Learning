<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantBranding;
use App\Listeners\ConfigureTenantMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Database\Models\Domain;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $isTenantAdmin = $user->hasRole('tenant_admin');

        if ($isTenantAdmin) {
            // For tenant admins, show stats only for their tenant
            $totalUsers = User::where('tenant_id', $user->tenant_id)->count();
            $totalAdmins = User::where('tenant_id', $user->tenant_id)->role('tenant_admin')->count();
            $totalInstructors = User::where('tenant_id', $user->tenant_id)->role('instructor')->count();
            $totalStudents = User::where('tenant_id', $user->tenant_id)->role('student')->count();
            $recentUsers = User::where('tenant_id', $user->tenant_id)
                ->with('roles')
                ->latest()
                ->take(5)
                ->get();
        } else {
            // For super admin, show all stats
            $totalUsers = User::count();
            $totalAdmins = User::role('tenant_admin')->count();
            $totalInstructors = User::role('instructor')->count();
            $totalStudents = User::role('student')->count();
            $recentUsers = User::with('roles')->latest()->take(5)->get();
        }

        return Inertia::render(
            'Admin/Dashboard',
            compact(
                'totalUsers',
                'totalAdmins',
                'totalInstructors',
                'totalStudents',
                'recentUsers'
            )
        );
    }

    /**
     * List all users
     */
    public function users(Request $request)
    {
        $authUser = auth()->user();

        $query = User::query()
            ->with(['roles'])
            ->when(
                $authUser->hasRole('tenant_admin'),
                fn ($query) => $query->where('tenant_id', $authUser->tenant_id)
            );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('tenant_id', 'like', "%{$search}%")
                    ->orWhereHas('roles', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        $authUser = auth()->user();

        // Filter roles based on the user's role
        $roles = $authUser->hasRole('tenant_admin')
            ? Role::where('name', '!=', 'superadmin')->get()
            : Role::all();

        $tenants = $authUser->hasRole('tenant_admin')
            ? Tenant::where('id', $authUser->tenant_id)->get()
            : Tenant::all();

        return Inertia::render(
            'Admin/Users/Create',
            compact('roles', 'tenants')
        );
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $authUser = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],

            'role' => [
                'required',
                'string',
                $authUser->hasRole('tenant_admin')
                    ? Rule::in(['tenant_admin', 'instructor', 'student'])
                    : Rule::exists('roles', 'name'),
            ],

            'tenant_id' => [
                'required',
                'string',
                $authUser->hasRole('tenant_admin')
                    ? Rule::in([$authUser->tenant_id])
                    : Rule::exists('tenants', 'id'),
            ],

            'phone' => ['nullable', 'string', 'max:20'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'department' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        $authUser = auth()->user();

        // Tenant admin can only edit users in their tenant
        if (
            $authUser->hasRole('tenant_admin') &&
            $user->tenant_id !== $authUser->tenant_id
        ) {
            abort(403, 'Unauthorized');
        }

        $roles = $authUser->hasRole('tenant_admin')
            ? Role::whereIn(
                'name',
                ['tenant_admin', 'instructor', 'student']
            )->get()
            : Role::all();

        $tenants = $authUser->hasRole('tenant_admin')
            ? Tenant::where('id', $authUser->tenant_id)->get()
            : Tenant::all();

        return Inertia::render(
            'Admin/Users/Edit',
            compact('user', 'roles', 'tenants')
        );
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $authUser = auth()->user();

        // Tenant admin can only update users in their tenant
        if (
            $authUser->hasRole('tenant_admin') &&
            $user->tenant_id !== $authUser->tenant_id
        ) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'unique:users,email,' . $user->id,
            ],

            'role' => [
                'required',
                'string',
                $authUser->hasRole('tenant_admin')
                    ? Rule::in(['tenant_admin', 'instructor', 'student'])
                    : Rule::exists('roles', 'name'),
            ],

            'tenant_id' => [
                'required',
                'string',
                $authUser->hasRole('tenant_admin')
                    ? Rule::in([$authUser->tenant_id])
                    : Rule::exists('tenants', 'id'),
            ],

            'password' => ['nullable', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'department' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'tenant_id' => $validated['tenant_id'],
            'phone' => $validated['phone'],
            'birthday' => $validated['birthday'],
            'department' => $validated['department'],
            'address' => $validated['address'],
        ];

        // Only update password if provided
        if ($validated['password']) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update role
        $user->syncRoles($validated['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        $authUser = auth()->user();

        // Tenant admin can only delete users in their tenant
        if (
            $authUser->hasRole('tenant_admin') &&
            $user->tenant_id !== $authUser->tenant_id
        ) {
            abort(403, 'Unauthorized');
        }

        $userName = $user->name;

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User '$userName' deleted successfully!");
    }

    // ========== COURSE MANAGEMENT (Tenant Admin) ==========

    /**
     * List courses for approval
     */
    public function courses()
    {
        $user = auth()->user();

        $courses = Course::where('tenant_id', $user->tenant_id)
            ->with(['instructor', 'sections'])
            ->paginate(10);

        return Inertia::render(
            'Admin/Courses/Index',
            compact('courses')
        );
    }

    /**
     * Show course detail
     */
    public function showCourse(Course $course)
    {
        $user = auth()->user();

        // Authorize - only tenant admin can view courses in their tenant
        if ($user->tenant_id !== $course->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $course->load([
            'instructor',
            'sections.lessons.videos',
        ]);

        return Inertia::render(
            'Admin/Courses/Show',
            compact('course')
        );
    }

    /**
     * Publish course
     */
    public function publishCourse(Request $request, Course $course)
    {
        $user = auth()->user();

        // Verify tenant ownership
        if ($course->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $course->update([
            'published' => true,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                "Course '{$course->title}' published successfully!"
            );
    }

    /**
     * Unpublish course
     */
    public function unpublishCourse(Request $request, Course $course)
    {
        $user = auth()->user();

        // Verify tenant ownership
        if ($course->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $course->update([
            'published' => false,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                "Course '{$course->title}' unpublished successfully!"
            );
    }

    // ========== TENANT MANAGEMENT ==========

    /**
     * List all tenants
     */
    public function tenants(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Tenant::with(['domains', 'plan', 'users' => function ($q) {
            $q->whereHas('roles', fn ($r) => $r->where('name', 'tenant_admin'));
        }]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tenants = $query->latest()->paginate(10)->withQueryString();

        $counts = [
            'all' => Tenant::count(),
            'pending' => Tenant::where('status', 'pending')->count(),
            'active' => Tenant::where('status', 'active')->count(),
            'rejected' => Tenant::where('status', 'rejected')->count(),
        ];

        return Inertia::render(
            'Admin/Tenants/Index',
            compact('tenants', 'counts', 'status')
        );
    }

    /**
     * Approve a pending tenant
     */
    public function approveTenant(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'active',
            'payment_status' => 'verified',
            'paid_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                "Tenant '{$tenant->name}' berhasil disetujui, pembayaran e-Wallet diverifikasi, dan portal diaktifkan."
            );
    }

    /**
     * Reject a tenant registration
     */
    public function rejectTenant(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'rejected',
            'payment_status' => 'rejected',
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                "Pendaftaran tenant '{$tenant->name}' telah ditolak."
            );
    }

    /**
     * Show create tenant form
     */
    public function createTenant()
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    /**
     * Store new tenant
     */
    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'unique:domains,domain'],
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'id' => str()->slug($validated['name']),
            'name' => $validated['name'],
        ]);

        // Add domain
        $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return redirect()
            ->route('admin.tenants.index')
            ->with(
                'success',
                "Tenant '{$validated['name']}' created successfully!"
            );
    }

    /**
     * Show edit tenant form
     */
    public function editTenant(Tenant $tenant)
    {
        return Inertia::render(
            'Admin/Tenants/Edit',
            compact('tenant')
        );
    }

    /**
     * Update tenant
     */
    public function updateTenant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'domain' => [
                'required',
                'string',
                'unique:domains,domain,' .
                ($tenant->domains()->first()?->id ?? 0),
            ],
        ]);

        $tenant->update([
            'name' => $validated['name'],
        ]);

        if ($tenant->domains()->first()) {
            $tenant->domains()
                ->first()
                ->update([
                    'domain' => $validated['domain'],
                ]);
        }

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Show mail settings form
     */
    public function mailSettings()
    {
        $tenant = auth()->user()->tenant;

        return Inertia::render('Admin/Mail/Settings', [
            'mailSettings' => [
                'mail_from_address' => $tenant->mail_from_address,
                'mail_from_name' => $tenant->mail_from_name,
                'mail_host' => $tenant->mail_host,
                'mail_port' => $tenant->mail_port,
                'mail_username' => $tenant->mail_username,
                'mail_encryption' => $tenant->mail_encryption,
                // Password sengaja TIDAK dikirim ke frontend
            ],
        ]);
    }

    /**
     * Update mail settings
     */
    public function updateMailSettings(Request $request)
    {
        $validated = $request->validate([
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl'],
        ]);

        // Ambil tenant milik user yang sedang login
        $tenant = auth()->user()->tenant;

        $settings = [
            'mail_from_address' => $validated['mail_from_address'] ?? null,
            'mail_from_name' => $validated['mail_from_name'] ?? null,
            'mail_host' => $validated['mail_host'] ?? null,
            'mail_port' => $validated['mail_port'] ?? null,
            'mail_username' => $validated['mail_username'] ?? null,
            'mail_encryption' => $validated['mail_encryption'] ?: null,
        ];

        // Tenant uses Stancl's virtual columns: set attributes on the model so
        // its save hook can merge them into the existing JSON data safely.
        if (! empty($validated['mail_password'])) {
            $settings['mail_password_encrypted'] = Crypt::encryptString(
                $validated['mail_password']
            );
        }

        $tenant->forceFill($settings)->save();

        /*
         * Kembali ke halaman Mail Settings
         * dengan flash message sukses.
         */
        return redirect()
            ->route('admin.mail-settings')
            ->with(
                'success',
                'Mail settings updated successfully.'
            );
    }

    /** Send a test message to the requested recipient using tenant SMTP. */
    public function testMailSettings(Request $request, ConfigureTenantMail $mailConfigurator)
    {
        $tenant = $request->user()->tenant;
        $validated = $request->validate([
            'recipient' => ['required', 'email', 'max:255'],
        ]);
        $recipient = $validated['recipient'];

        if (! $tenant) {
            return back()->with('error', 'A tenant is required to test SMTP.');
        }

        if (empty($tenant->mail_host)) {
            return back()->with('error', 'Save an SMTP host before sending a test email.');
        }

        try {
            $mailConfigurator->configure($tenant);

            Mail::mailer('smtp')->raw(
                'This is a test email from KITsuluh. Your tenant SMTP settings can send mail.',
                function ($message) use ($recipient) {
                    $message->to($recipient)->subject('KITsuluh SMTP test');
                }
            );
        } catch (\Throwable $exception) {
            Log::warning('Tenant SMTP test failed', [
                'tenant_id' => $tenant->id,
                'exception' => $exception::class,
            ]);

            return back()->with('error', 'SMTP test failed. Check the host, port, encryption, credentials, and application logs.');
        }

        return back()->with('success', "Test email sent to {$recipient}.");
    }

    /**
     * Delete tenant
     */
    public function deleteTenant(Tenant $tenant)
    {
        $tenantName = $tenant->name;

        $tenant->domains()->delete();
        $tenant->delete();

        return redirect()
            ->route('admin.tenants.index')
            ->with(
                'success',
                "Tenant '{$tenantName}' deleted successfully!"
            );
    }

    /**
     * Branding tenant
     */
    public function branding()
    {
        $tenant = auth()->user()->tenant;

        abort_unless($tenant, 403);

        $branding = $tenant->branding;

        return Inertia::render('Admin/Branding/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],

            'branding' => [
                'agency_name' => $branding?->agency_name ?? '',
                'tagline' => $branding?->tagline ?? '',

                'logo_path' => $branding?->logo_path,

                'certificate_logo_path' =>
                    $branding?->certificate_logo_path,

                'logo_url' => $branding?->logo_path
                    ? Storage::disk('public')->url(
                        $branding->logo_path
                    )
                    : null,

                'certificate_logo_url' =>
                    $branding?->certificate_logo_path
                        ? Storage::disk('public')->url(
                            $branding->certificate_logo_path
                        )
                        : null,
            ],
        ]);
    }

    /**
 * Update tenant branding
 */
    public function updateBranding(Request $request)
    {
        $tenant = auth()->user()->tenant;

        abort_unless($tenant, 403);

        $validated = $request->validate([
            'agency_name' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],

            'logo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'certificate_logo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        /*
        * Ambil atau buat branding tenant.
        */
        $branding = $tenant->branding;

        if (!$branding) {
            $branding = TenantBranding::create([
                'tenant_id' => $tenant->id,
                'agency_name' => $validated['agency_name'] ?? null,
                'tagline' => $validated['tagline'] ?? null,
            ]);
        } else {
            $branding->update([
                'agency_name' => $validated['agency_name'] ?? null,
                'tagline' => $validated['tagline'] ?? null,
            ]);
        }

        /*
        * Process main logo.
        *
        * PNG  -> PNG
        * WebP -> WebP
        * JPG  -> PNG
        */
        if ($request->hasFile('logo')) {
            $oldLogoPath = $branding->logo_path;

            $newLogoPath = $this->processBrandingImage(
                $request->file('logo'),
                $tenant->id,
                'logo'
            );

            $branding->update([
                'logo_path' => $newLogoPath,
            ]);

            /*
            * Hapus file lama hanya setelah file baru
            * berhasil diproses dan disimpan.
            */
            if (
                $oldLogoPath &&
                $oldLogoPath !== $newLogoPath &&
                Storage::disk('public')->exists($oldLogoPath)
            ) {
                Storage::disk('public')->delete($oldLogoPath);
            }
        } elseif ($request->boolean('remove_logo')) {
            $oldLogoPath = $branding->logo_path;

            $branding->update([
                'logo_path' => null,
            ]);

            if (
                $oldLogoPath &&
                Storage::disk('public')->exists($oldLogoPath)
            ) {
                Storage::disk('public')->delete($oldLogoPath);
            }
        }

        /*
        * Process certificate logo.
        *
        * PNG  -> PNG
        * WebP -> WebP
        * JPG  -> PNG
        */
        if ($request->hasFile('certificate_logo')) {
            $oldCertificateLogoPath = $branding->certificate_logo_path;

            $newCertificateLogoPath = $this->processBrandingImage(
                $request->file('certificate_logo'),
                $tenant->id,
                'certificate-logo'
            );

            $branding->update([
                'certificate_logo_path' => $newCertificateLogoPath,
            ]);

            /*
            * Hapus file lama hanya setelah file baru
            * berhasil diproses dan disimpan.
            */
            if (
                $oldCertificateLogoPath &&
                $oldCertificateLogoPath !== $newCertificateLogoPath &&
                Storage::disk('public')->exists($oldCertificateLogoPath)
            ) {
                Storage::disk('public')->delete(
                    $oldCertificateLogoPath
                );
            }
        } elseif ($request->boolean('remove_certificate_logo')) {
            $oldCertificateLogoPath = $branding->certificate_logo_path;

            $branding->update([
                'certificate_logo_path' => null,
            ]);

            if (
                $oldCertificateLogoPath &&
                Storage::disk('public')->exists($oldCertificateLogoPath)
            ) {
                Storage::disk('public')->delete(
                    $oldCertificateLogoPath
                );
            }
        }

        return redirect()
            ->route('admin.branding')
            ->with(
                'success',
                'Branding berhasil diperbarui.'
            );
    }

    private function processBrandingImage(
        $file,
        string $tenantId,
        string $name
    ): string {
        $mime = strtolower($file->getMimeType());

        /*
         * Create GD source image based on the actual MIME type.
         */
        $source = match ($mime) {
            'image/png' => @imagecreatefrompng(
                $file->getRealPath()
            ),

            'image/webp' => function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($file->getRealPath())
                : false,

            'image/jpeg' => @imagecreatefromjpeg(
                $file->getRealPath()
            ),

            default => false,
        };

        if (!$source) {
            throw new \RuntimeException(
                'Gagal memproses gambar branding. ' .
                'Pastikan ekstensi GD PHP aktif dan file gambar valid.'
            );
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width < 1 || $height < 1) {
            imagedestroy($source);

            throw new \RuntimeException(
                'Dimensi gambar branding tidak valid.'
            );
        }

        /*
         * Create true-color canvas with alpha support.
         *
         * Dimensions are exactly the same as the original.
         */
        $image = imagecreatetruecolor($width, $height);

        if (!$image) {
            imagedestroy($source);

            throw new \RuntimeException(
                'Gagal membuat canvas gambar branding.'
            );
        }

        /*
         * Make canvas transparent.
         */
        imagealphablending($image, false);

        $transparent = imagecolorallocatealpha(
            $image,
            255,
            255,
            255,
            127
        );

        imagefill(
            $image,
            0,
            0,
            $transparent
        );

        /*
         * Copy original image.
         *
         * Alpha information from PNG/WebP is preserved.
         */
        imagecopy(
            $image,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height
        );

        imagedestroy($source);

        $backgroundTolerance = 18;

        imagealphablending($image, false);

        $backgroundWhite = imagecolorallocatealpha(
            $image,
            255,
            255,
            255,
            0
        );

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);

                $alpha = ($rgba >> 24) & 0x7F;

                /*
                 * Keep already-transparent pixels untouched.
                 */
                if ($alpha >= 120) {
                    continue;
                }

                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                if (
                    $red >= 255 - $backgroundTolerance &&
                    $green >= 255 - $backgroundTolerance &&
                    $blue >= 255 - $backgroundTolerance
                ) {
                    imagesetpixel(
                        $image,
                        $x,
                        $y,
                        $backgroundWhite
                    );
                }
            }
        }

        /*
         * Transparent color used by flood-fill.
         */
        $transparentBackground = imagecolorallocatealpha(
            $image,
            255,
            255,
            255,
            127
        );

        imagealphablending($image, false);

        for ($x = 0; $x < $width; $x++) {
            $this->removeConnectedWhiteBackground(
                $image,
                $x,
                0,
                $backgroundTolerance,
                $transparentBackground
            );

            if ($height > 1) {
                $this->removeConnectedWhiteBackground(
                    $image,
                    $x,
                    $height - 1,
                    $backgroundTolerance,
                    $transparentBackground
                );
            }
        }

        /*
         * Left and right edges.
         */
        for ($y = 1; $y < $height - 1; $y++) {
            $this->removeConnectedWhiteBackground(
                $image,
                0,
                $y,
                $backgroundTolerance,
                $transparentBackground
            );

            if ($width > 1) {
                $this->removeConnectedWhiteBackground(
                    $image,
                    $width - 1,
                    $y,
                    $backgroundTolerance,
                    $transparentBackground
                );
            }
        }

        /*
         * Preserve original format whenever possible.
         *
         * JPEG cannot store transparency, so it becomes PNG.
         */
        $extension = match ($mime) {
            'image/png' => 'png',

            'image/webp' => function_exists('imagewebp')
                ? 'webp'
                : 'png',

            default => 'png',
        };

        /*
         * UUID avoids filename collisions.
         */
        $filename = "{$name}-" . Str::uuid() . ".{$extension}";

        $path = "tenants/{$tenantId}/branding/{$filename}";

        /*
         * Ensure alpha is written correctly.
         */
        imagealphablending($image, false);
        imagesavealpha($image, true);

        /*
         * Encode image into memory first.
         *
         * We only save the file to Storage after successful encoding.
         */
        ob_start();

        $saved = match ($extension) {
            'webp' => imagewebp(
                $image,
                null,
                90
            ),

            default => imagepng(
                $image,
                null,
                6
            ),
        };

        $contents = ob_get_clean();

        imagedestroy($image);

        if (
            !$saved ||
            $contents === false ||
            $contents === ''
        ) {
            throw new \RuntimeException(
                'Gagal menyimpan hasil pemrosesan gambar branding.'
            );
        }

        /*
         * Store processed image.
         */
        Storage::disk('public')->put(
            $path,
            $contents
        );

        return $path;
    }

    /**
     * Remove a white background region when the supplied pixel
     * is connected to that background.
     *
     * imagefill() fills only the connected region having the same
     * color. Because near-white pixels were normalized beforehand,
     * enclosed white logo elements remain untouched.
     */
    private function removeConnectedWhiteBackground(
        $image,
        int $x,
        int $y,
        int $backgroundTolerance,
        $transparentColor
    ): void {
        $pixel = imagecolorat($image, $x, $y);

        $alpha = ($pixel >> 24) & 0x7F;

        /*
         * Already transparent.
         */
        if ($alpha >= 120) {
            return;
        }

        $red = ($pixel >> 16) & 0xFF;
        $green = ($pixel >> 8) & 0xFF;
        $blue = $pixel & 0xFF;

        /*
         * Only flood-fill near-white edge regions.
         */
        if (
            $red < 255 - $backgroundTolerance ||
            $green < 255 - $backgroundTolerance ||
            $blue < 255 - $backgroundTolerance
        ) {
            return;
        }

        /*
         * imagefill() removes the entire connected normalized-white
         * region while leaving enclosed white regions untouched.
         */
        imagefill(
            $image,
            $x,
            $y,
            $transparentColor
        );
    }
}
