<!-- Sidebar -->
<aside class="w-64 bg-gray-900 border-r border-gray-800 min-h-screen sticky top-0">
    <div class="p-6">
        <h2 class="text-lg font-bold text-white">Admin Dashboard</h2>
        <p class="text-sm text-gray-400 mt-1">{{ Auth::user()->tenant->name ?? 'System' }}</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="px-4 space-y-2">
        <!-- Dashboard Link -->
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 transition' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4V9"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Users Management Link -->
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 transition' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646M12 16H8a4 4 0 00-4 4v2h16v-2a4 4 0 00-4-4h-4z"></path>
            </svg>
            <span>Manage Users</span>
        </a>

        <!-- Tenants Link (only for superadmin) -->
        @if(Auth::user()->hasRole('superadmin'))
            <a href="{{ route('admin.tenants.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.tenants*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 transition' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                </svg>
                <span>Manage Tenants</span>
            </a>
        @endif

        <!-- Courses Link -->
        <a href="{{ route('admin.courses.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.courses*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 transition' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"></path>
            </svg>
            <span>Course Approval</span>
        </a>
    </nav>

    <!-- Divider -->
    <div class="my-6 border-t border-gray-700"></div>

    <!-- Tenant Info -->
    <div class="px-4 py-4 bg-gray-800 rounded-lg mx-4">
        <p class="text-xs font-semibold text-gray-400 uppercase">{{ Auth::user()->hasRole('superadmin') ? 'System Info' : 'Current Tenant' }}</p>
        <p class="text-sm font-medium text-white mt-2">{{ Auth::user()->tenant->name ?? 'System' }}</p>
        @if(Auth::user()->hasRole('superadmin'))
            <p class="text-xs text-gray-400 mt-1">Super Admin</p>
        @else
            <p class="text-xs text-gray-400 mt-1">Tenant Admin</p>
        @endif
    </div>
</aside>
