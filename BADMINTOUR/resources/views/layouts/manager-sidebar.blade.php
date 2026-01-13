<!-- Manager Sidebar -->
<div class="w-64 bg-[#D8E3E7] h-screen flex flex-col">
    <!-- Logo Section -->
    <div class="p-4 flex justify-center">
        <img src="{{ asset('images/logo.jpg') }}" alt="Badminton Tournament Management Platform" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover mx-auto">
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-2">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('manager.dashboard') }}" class="{{ request()->routeIs('manager.dashboard') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <a href="{{ route('manager.tournaments') }}" class="{{ request()->routeIs('manager.tournaments') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    Tournaments
                </a>
            </li>
            <li>
                <a href="{{ route('manager.matches') }}" class="{{ request()->routeIs('manager.matches') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                    </svg>
                    Matches
                </a>
            </li>
            <li>
                <a href="{{ route('manager.ranking') }}" class="{{ request()->routeIs('manager.ranking') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Ranking
                </a>
            </li>
            <li>
                <a href="{{ route('manager.club') }}" class="{{ request()->routeIs('manager.club') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    My Club
                </a>
            </li>
            <li>
                <a href="{{ route('manager.clubs') }}" class="{{ request()->routeIs('manager.clubs') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Clubs
                </a>
            </li>
            <li>
                <a href="{{ route('manager.players') }}" class="{{ request()->routeIs('manager.players') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Players
                </a>
            </li>
            <li>
                <a href="{{ route('manager.profile') }}" class="{{ request()->routeIs('manager.profile') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile
                </a>
            </li>
            <li>
                <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'bg-[#1B4965] text-white' : 'text-gray-700 hover:bg-gray-300' }} flex items-center px-4 py-2.5 rounded transition duration-150">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Notifications
                    @php
                        $unreadCount = auth()->user()->notifications()->whereNull('read_at')->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="ml-auto bg-[#C85A54] text-white text-xs font-bold rounded-full px-2 py-0.5">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </nav>

    <!-- Logout Button -->
    <div class="p-2 pb-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center px-4 py-2.5 text-gray-700 hover:bg-gray-300 rounded transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </button>
        </form>
    </div>
</div>
