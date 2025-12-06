@php
    $notifications = auth()->user()->notifications()->latest()->limit(5)->get();
    $unreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
    
    $notificationConfig = [
        'club_join_request' => [
            'icon' => '<path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>',
            'color' => 'bg-purple-500'
        ],
        'club_join_approved' => [
            'icon' => '<path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
            'color' => 'bg-[#2C5F4F]'
        ],
        'club_join_rejected' => [
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
            'color' => 'bg-[#C85A54]'
        ],
        'default' => [
            'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>',
            'color' => 'bg-blue-500'
        ]
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden" x-data="{ notificationsOpen: false }">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#D8E3E7] flex flex-col">
            <!-- Logo -->
            <div class="p-4">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-16 h-16 object-contain">
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('player.dashboard') }}" 
                           class="flex items-center px-4 py-2.5 rounded {{ request()->routeIs('player.dashboard') || request()->routeIs('manager.dashboard') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} transition">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tournaments.index') }}" 
                           class="flex items-center px-4 py-2.5 rounded {{ request()->is('tournaments*') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} transition">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z"/>
                            </svg>
                            Tournaments
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('ranking.index') }}" 
                           class="flex items-center px-4 py-2.5 rounded {{ request()->is('ranking*') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} transition">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            Ranking
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('clubs.index') }}" 
                           class="flex items-center px-4 py-2.5 rounded {{ request()->is('clubs*') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} transition">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            Clubs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('players.index') }}" 
                           class="flex items-center px-4 py-2.5 rounded {{ request()->is('players*') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} transition">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            Players
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.index') }}" 
                           class="flex items-center px-4 py-2.5 rounded {{ request()->is('profile') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} transition">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Profile
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Logout -->
            <div class="p-2 pb-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-300 rounded transition">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $greeting ?? 'WELCOME, PLAYER!' }}</h1>
                        <p class="text-sm text-gray-500">Today: {{ now()->format('F j, Y') }}</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search Bar -->
                        <div class="relative">
                            <input 
                                type="text" 
                                placeholder="Search for tournaments, clubs, players..."
                                class="w-96 px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400 text-sm"
                            >
                            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        
                        <!-- Notification Bell -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <!-- Unread indicator badge -->
                                @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-[#C85A54] rounded-full border-2 border-white"></span>
                                @endif
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border-2 border-[#D4A574] z-50"
                                 style="display: none;">
                                
                                <!-- Header -->
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-900">Notifications</h3>
                                </div>

                                <!-- Notifications List -->
                                <div class="max-h-96 overflow-y-auto">
                                    @forelse($notifications as $notification)
                                    @php
                                        $config = $notificationConfig[$notification->type] ?? $notificationConfig['default'];
                                        $isUnread = is_null($notification->read_at);
                                    @endphp
                                    <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-10 h-10 {{ $config['color'] }} rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    {!! $config['icon'] !!}
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm {{ $isUnread ? 'font-bold text-gray-900' : 'font-semibold text-gray-700' }}">{{ $notification->title }}</p>
                                                    @if($isUnread)
                                                    <span class="w-2 h-2 bg-[#C85A54] rounded-full"></span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-600 mt-1">{{ $notification->message }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="px-4 py-8 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <p class="text-sm">No notifications yet</p>
                                    </div>
                                    @endforelse
                                </div>

                                <!-- Footer -->
                                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 space-y-2">
                                    <button class="w-full text-sm text-[#1B4965] hover:text-[#2C5F4F] font-semibold text-center py-1 transition">
                                        Mark all as read
                                    </button>
                                    <a href="/notifications" class="block w-full">
                                        <button class="w-full bg-[#C85A54] text-white px-4 py-2 rounded-lg hover:bg-[#B54A44] transition text-sm font-semibold">
                                            View all notifications
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
