<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KITsuluh')</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logok.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Sidebar Navigation -->
    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path></svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex ml-2 md:mr-24">
                        <img src="{{ asset('img/logok.png') }}" alt="Logo" class="h-8 w-8 mr-2 my-auto">
                        <span class="self-center text-2xl font-semibold whitespace-nowrap">KITsuluh</span>
                    </a>
                </div>

                <!-- Top bar menu -->
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <a href="{{ route('search.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </a>

                    <!-- Notifications -->
                    <div class="relative">
                        <button type="button" id="notification-btn" class="relative text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span id="notification-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full hidden">0</span>
                        </button>

                        <!-- Notifications Dropdown -->
                        <div id="dropdown-notifications" class="hidden absolute right-0 mt-2 z-50 text-base list-none bg-white divide-y divide-gray-100 rounded shadow w-80">
                            <div class="px-4 py-3 font-semibold text-sm text-gray-900">Notifications</div>
                            <div id="notification-list" class="max-h-64 overflow-y-auto">
                                <p class="px-4 py-3 text-sm text-gray-500">Loading...</p>
                            </div>
                            <div class="px-4 py-2 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User menu button -->
                    <div class="flex items-center ml-3">
                        <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                    </div>
                    <!-- Dropdown menu -->
                    <div id="dropdown-user" class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow">
                        <div class="px-4 py-3" role="none">
                            <p class="text-sm text-gray-900 font-semibold" role="none">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-sm text-gray-500" role="none">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                        <ul class="py-2" role="none">
                            <li>
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">My Dashboard</a>
                            </li>
                            <li>
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">My Profile</a>
                            </li>
                        </ul>
                        <ul class="py-2" role="none">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
            <ul class="space-y-2 font-medium">
                <!-- Universal Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-100' : '' }}">
                        <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>

                <!-- User Profile -->
                <li>
                    <a href="{{ route('profile.show') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('profile.*') ? 'bg-gray-100' : '' }}">
                        <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                        <span class="ml-3">My Profile</span>
                    </a>
                </li>

                <!-- Admin Tenant Menu -->
                @if(auth()->user()->hasRole('tenant_admin'))
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path></svg>
                            <span class="ml-3">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.courses.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.courses.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM15 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z"></path></svg>
                            <span class="ml-3">Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.learning-paths.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.learning-paths.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10.102A7.969 7.969 0 015.5 14c1.669 0 3.218-.584 4.5-1.548V4.804z"></path><path d="M15 4.804A7.968 7.968 0 0011.5 4c-1.255 0-2.443.29-3.5.804v10.102A7.97 7.97 0 0111.5 14c1.669 0 3.218-.584 4.5-1.548V4.804z"></path></svg>
                            <span class="ml-3">Learning Paths</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.learning-paths.analytics.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.learning-paths.analytics.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                            <span class="ml-3">Path Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.assessment-analytics.tenant') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.assessment-analytics.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="ml-3">Assessment Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                            <span class="ml-3">Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.tags.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.tags.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>
                            <span class="ml-3">Tags</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                            <span class="ml-3">Audit Logs</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.bulk.import-users') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.bulk.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="ml-3">Bulk Ops</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.mail-settings') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.mail-settings') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>
                            <span class="ml-3">Mail Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.badges.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.badges.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="ml-3">Badges</span>
                        </a>
                    </li>
                @endif

                <!-- Superadmin Menu -->
                @if(auth()->user()->hasRole('superadmin'))
                    <li>
                        <a href="{{ route('admin.tenants.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.tenants.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path></svg>
                            <span class="ml-3">Tenants</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path></svg>
                            <span class="ml-3">Users</span>
                        </a>
                    </li>
                @endif

                <!-- Instructor Menu -->
                @if(auth()->user()->hasRole('instructor'))
                    <li>
                        <a href="{{ route('instructor.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('instructor.dashboard') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            <span class="ml-3">Teaching</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor.courses.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('instructor.courses.*') && !request()->routeIs('instructor.assessment-analytics.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM15 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z"></path></svg>
                            <span class="ml-3">My Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor.assessment-analytics.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('instructor.assessment-analytics.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="ml-3">Assessment Analytics</span>
                        </a>
                    </li>
                @endif

                <!-- All Users: Discussions -->
                <li>
                    <a href="{{ route('discussions.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('discussions.*') ? 'bg-gray-100' : '' }}">
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z" clip-rule="evenodd"></path></svg>
                        <span class="ml-3">Discussions</span>
                    </a>
                </li>

                <!-- Student Menu -->
                @if(auth()->user()->hasRole('student'))
                    <li>
                        <a href="{{ route('student.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.dashboard') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            <span class="ml-3">Learning</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.courses.list') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.courses.list') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM15 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z"></path></svg>
                            <span class="ml-3">Courses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.enrollments') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.enrollments') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2H3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1h-2a1 1 0 000 2h1v11H4V5z" clip-rule="evenodd"></path></svg>
                            <span class="ml-3">My Learning</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.learning-paths.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.learning-paths.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10.102A7.969 7.969 0 015.5 14c1.669 0 3.218-.584 4.5-1.548V4.804z"></path><path d="M15 4.804A7.968 7.968 0 0011.5 4c-1.255 0-2.443.29-3.5.804v10.102A7.97 7.97 0 0111.5 14c1.669 0 3.218-.584 4.5-1.548V4.804z"></path></svg>
                            <span class="ml-3">Learning Paths</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.grades.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.grades.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="ml-3">My Grades</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.live-classes.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.live-classes.*') ? 'bg-gray-100' : '' }}">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm4 2v4h8V8H6z"></path></svg>
                            <span class="ml-3">Live Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.gamification.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.gamification.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="ml-3">Gamification</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.leaderboard.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 {{ request()->routeIs('student.leaderboard.*') ? 'bg-gray-100' : '' }}">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a3 3 0 00-3 3v2H5a3 3 0 00-3 3v5a3 3 0 003 3h10a3 3 0 003-3v-5a3 3 0 00-3-3h-2V5a3 3 0 00-3-3zM5 10h2v5H5v-5zm4-5v10h2V5H9zm4 3v7h2V8h-2z"></path></svg>
                            <span class="ml-3">Leaderboard</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </aside>

    <!-- Main content -->
    <div class="p-4 sm:ml-64 pt-20">
        @yield('content')
    </div>

<script>
    function loadUnreadCount() {
        fetch('{{ route("notifications.unread-count") }}')
            .then(r => r.json())
            .then(d => {
                const badge = document.getElementById('notification-badge');
                if (d.count > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = d.count;
                } else {
                    badge.classList.add('hidden');
                }
            });
    }

    function loadNotifications() {
        fetch('{{ route("notifications.index") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(r => r.text())
            .then(html => {
                const list = document.getElementById('notification-list');
                if (html.trim()) {
                    list.innerHTML = html;
                } else {
                    list.innerHTML = '<p class="px-4 py-3 text-sm text-gray-500">No notifications</p>';
                }
            });
    }

    function initNotifications() {
        loadUnreadCount();
        setInterval(loadUnreadCount, 30000);

        const notifBtn = document.getElementById('notification-btn');
        const notifDropdown = document.getElementById('dropdown-notifications');

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isHidden = notifDropdown.classList.contains('hidden');
                notifDropdown.classList.toggle('hidden');
                if (isHidden) {
                    loadNotifications();
                }
            });

            document.addEventListener('click', function (e) {
                if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
                    notifDropdown.classList.add('hidden');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
</script>
</body>
</html>
