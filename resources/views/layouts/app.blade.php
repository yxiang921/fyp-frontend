<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Real-Time Facial Recognition-Based Monitoring and Alert System">
    <title>@yield('title', 'FRMS') — Facial Recognition Monitoring System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Mobile Sidebar Overlay -->
    <div x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
        <div x-show="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false" x-cloak></div>

        <!-- Sidebar -->
        <aside
            class="sidebar"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="sidebar-brand">
                <div class="w-9 h-9 bg-white/15 rounded flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-shield-halved text-white text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold leading-tight">FRMS</p>
                    <p class="text-[10px] text-gray-400 leading-tight">Monitoring & Alert System</p>
                </div>
            </div>

            <nav class="sidebar-nav" x-data="{ activeGroup: null }">
                @auth
                    @php $role = auth()->user()->role; @endphp

                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Dashboard</span>
                    </a>

                    {{-- Super Admin & PDRM Officer: Case Management --}}
                    @if(in_array($role->value, ['super_admin', 'pdrm_officer']))
                    <div class="sidebar-section">Case Management</div>

                    <a href="{{ route('missing-persons.index') }}" class="sidebar-link {{ request()->routeIs('missing-persons*') && request('status') != 'pending' ? 'active' : '' }}">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Missing Persons</span>
                    </a>
                    @endif

                    {{-- PDRM Officer: Pending Verifications --}}
                    @if(in_array($role->value, ['super_admin', 'pdrm_officer']))
                        @php $pendingCount = \App\Models\MissingPerson::where('status', 'pending')->count(); @endphp
                        <a href="{{ route('missing-persons.index', ['status' => 'pending']) }}" class="sidebar-link {{ request('status') == 'pending' ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Pending Verifications</span>
                            @if($pendingCount > 0)
                            <span class="ml-auto badge badge-yellow text-[10px]">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    @endif

                    {{-- Super Admin & PDRM Officer: Monitoring --}}
                    @if(in_array($role->value, ['super_admin', 'pdrm_officer']))
                    <div class="sidebar-section">Monitoring</div>

                    <a href="{{ route('detections.index') }}" class="sidebar-link {{ request()->routeIs('detections*') ? 'active' : '' }}">
                        <i class="fa-solid fa-camera-retro"></i>
                        <span>Detections</span>
                    </a>

                    <a href="{{ route('alerts.index') }}" class="sidebar-link {{ request()->routeIs('alerts*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell"></i>
                        <span>Alerts</span>
                    </a>

                    <a href="{{ route('cameras.index') }}" class="sidebar-link {{ request()->routeIs('cameras*') ? 'active' : '' }}">
                        <i class="fa-solid fa-video"></i>
                        <span>Cameras</span>
                    </a>

                    <a href="{{ route('email-logs.index') }}" class="sidebar-link {{ request()->routeIs('email-logs*') ? 'active' : '' }}">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Email Logs</span>
                    </a>
                    @endif

                    {{-- Patrol Unit: Assigned Alerts --}}
                    @if($role->value === 'patrol_unit')
                    <div class="sidebar-section">Field Operations</div>

                    <a href="{{ route('alerts.index') }}" class="sidebar-link {{ request()->routeIs('alerts*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell"></i>
                        <span>Assigned Alerts</span>
                    </a>
                    @endif

                    {{-- Super Admin & PDRM Officer: Verifications --}}
                    @if(in_array($role->value, ['super_admin', 'pdrm_officer']))
                    <div class="sidebar-section">Verification</div>

                    <a href="{{ route('verifications.index') }}" class="sidebar-link {{ request()->routeIs('verifications*') ? 'active' : '' }}">
                        <i class="fa-solid fa-check-to-slot"></i>
                        <span>Verifications</span>
                    </a>
                    @endif

                    {{-- Public User: My Reports --}}
                    @if($role->value === 'public_user')
                    <div class="sidebar-section">Missing Person Reports</div>

                    <a href="{{ route('public.cases') }}" class="sidebar-link {{ request()->routeIs('public.cases*') || request()->routeIs('public.case*') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i>
                        <span>My Reports</span>
                    </a>

                    <a href="{{ route('public.report-missing') }}" class="sidebar-link {{ request()->routeIs('public.report-missing*') ? 'active' : '' }}">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Report Missing Person</span>
                    </a>
                    @endif

                    {{-- Super Admin: Administration --}}
                    @if($role->value === 'super_admin')
                    <div class="sidebar-section">Administration</div>

                    <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>User Management</span>
                    </a>

                    <a href="{{ route('audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('audit-logs*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Audit Logs</span>
                    </a>
                    @endif
                @endauth
            </nav>

            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
                <p class="text-[10px] text-gray-500 text-center leading-relaxed">
                    Facial recognition results are not final identification.<br>
                    Identity confirmation requires human review.
                </p>
            </div>
        </aside>

        <!-- Header -->
        <header class="header">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="hidden sm:block">
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Notification Bell --}}
                @auth
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700 p-1.5">
                        <i class="fa-regular fa-bell text-lg"></i>
                        @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                        @if($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 bg-red-600 text-white text-[10px] font-bold w-4.5 h-4.5 rounded-full flex items-center justify-center">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                        @endif
                    </button>
                    <div x-show="open" x-cloak class="notif-dropdown">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-900">Notifications</span>
                            <a href="{{ route('notifications.index') }}" class="text-xs text-navy hover:underline">View all</a>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notif)
                            <div class="px-4 py-3 hover:bg-gray-50 {{ $notif->isUnread() ? 'bg-blue-50/50' : '' }}">
                                <p class="text-sm font-medium text-gray-900">{{ $notif->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $notif->message }}</p>
                                <p class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            @empty
                            <div class="px-4 py-6 text-center">
                                <p class="text-sm text-gray-400">No notifications</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endauth

                {{-- User Dropdown --}}
                @auth
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-8 h-8 rounded-full">
                        <span class="hidden md:block font-medium">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden md:block"></i>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded shadow-sm z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fa-regular fa-user w-4 mr-2"></i> Profile
                            </a>
                            <a href="{{ route('profile.security') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-lock w-4 mr-2"></i> Security
                            </a>
                        </div>
                        <div class="border-t border-gray-200 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                    <i class="fa-solid fa-right-from-bracket w-4 mr-2"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-content">
                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="alert alert-success mb-4">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
                @endif
                @if(session('warning'))
                <div class="alert alert-warning mb-4">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger mb-4">
                    <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
                </div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>

            <footer class="px-4 lg:px-6 py-4 text-center text-xs text-gray-400 border-t border-gray-200 mt-8">
                <p>&copy; {{ date('Y') }} Real-Time Facial Recognition-Based Monitoring and Alert System (FRMS).</p>
                <p class="mt-1">This is a prototype system. Facial recognition results are simulated and are not final identification. All identity confirmations require human review and verification.</p>
            </footer>
        </main>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
