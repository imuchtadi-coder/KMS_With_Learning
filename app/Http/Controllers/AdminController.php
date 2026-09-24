<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
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
            $recentUsers = User::where('tenant_id', $user->tenant_id)->with('roles')->latest()->take(5)->get();
        } else {
            // For super admin, show all stats
            $totalUsers = User::count();
            $totalAdmins = User::role('tenant_admin')->count();
            $totalInstructors = User::role('instructor')->count();
            $totalStudents = User::role('student')->count();
            $recentUsers = User::with('roles')->latest()->take(5)->get();
        }

        return Inertia::render('Admin/Dashboard', compact('totalUsers', 'totalAdmins', 'totalInstructors', 'totalStudents', 'recentUsers'));
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

        return Inertia::render('Admin/Users/Create', compact('roles', 'tenants'));
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
        if ($authUser->hasRole('tenant_admin') && $user->tenant_id !== $authUser->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $roles = $authUser->hasRole('tenant_admin')
            ? Role::whereIn('name', ['tenant_admin', 'instructor', 'student'])->get()
            : Role::all();
        $tenants = $authUser->hasRole('tenant_admin') ?
                  Tenant::where('id', $authUser->tenant_id)->get() :
                  Tenant::all();

        return Inertia::render('Admin/Users/Edit', compact('user', 'roles', 'tenants'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $authUser = auth()->user();

        // Tenant admin can only update users in their tenant
        if ($authUser->hasRole('tenant_admin') && $user->tenant_id !== $authUser->tenant_id) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
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

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        $authUser = auth()->user();

        // Tenant admin can only delete users in their tenant
        if ($authUser->hasRole('tenant_admin') && $user->tenant_id !== $authUser->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
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

        return Inertia::render('Admin/Courses/Index', compact('courses'));
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

        $course->load(['instructor', 'sections.lessons.videos']);

        return Inertia::render('Admin/Courses/Show', compact('course'));
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

        $course->update(['published' => true]);

        return redirect()->back()->with('success', "Course '{$course->title}' published successfully!");
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

        $course->update(['published' => false]);

        return redirect()->back()->with('success', "Course '{$course->title}' unpublished successfully!");
    }

    // ========== TENANT MANAGEMENT ==========

    /**
     * List all tenants
     */
    public function tenants()
    {
        $tenants = Tenant::with('domains')->paginate(10);

        return Inertia::render('Admin/Tenants/Index', compact('tenants'));
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

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$validated['name']}' created successfully!");
    }

    /**
     * Show edit tenant form
     */
    public function editTenant(Tenant $tenant)
    {
        return Inertia::render('Admin/Tenants/Edit', compact('tenant'));
    }

    /**
     * Update tenant
     */
    public function updateTenant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'unique:domains,domain,'.($tenant->domains()->first()?->id ?? 0)],
        ]);

        $tenant->update(['name' => $validated['name']]);
        if ($tenant->domains()->first()) {
            $tenant->domains()->first()->update(['domain' => $validated['domain']]);
        }

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant updated successfully!');
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
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,'],
        ]);

        // Ambil tenant milik user yang sedang login
        $tenant = auth()->user()->tenant;

        /*
         * Ambil data tenant yang sudah ada.
         * Jangan menimpa data tenant lain yang mungkin sudah tersimpan.
         */
        $data = $tenant->data ?? [];

        /*
         * Simpan Mail Settings ke dalam kolom JSON "data"
         */
        $data['mail_from_address'] = $validated['mail_from_address'];
        $data['mail_from_name'] = $validated['mail_from_name'];
        $data['mail_host'] = $validated['mail_host'];
        $data['mail_port'] = $validated['mail_port'];
        $data['mail_username'] = $validated['mail_username'];
        $data['mail_encryption'] = $validated['mail_encryption'] ?: null;

        /*
         * Password hanya diperbarui jika user mengisi password baru.
         *
         * Password disimpan dalam bentuk encrypted,
         * bukan plain text.
         */
        if (! empty($validated['mail_password'])) {
            $data['mail_password_encrypted'] = Crypt::encryptString(
                $validated['mail_password']
            );
        }

        /*
         * Simpan seluruh data kembali ke kolom "data" tenant.
         */
        $tenant->update([
            'data' => $data,
        ]);

        /*
         * Kembali ke halaman Mail Settings
         * dengan flash message sukses.
         */
        return redirect()
            ->route('admin.mail-settings')
            ->with('success', 'Mail settings updated successfully.');
    }

    /**
     * Delete tenant
     */
    public function deleteTenant(Tenant $tenant)
    {
        $tenantName = $tenant->name;
        $tenant->domains()->delete();
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$tenantName}' deleted successfully!");
    }
}
