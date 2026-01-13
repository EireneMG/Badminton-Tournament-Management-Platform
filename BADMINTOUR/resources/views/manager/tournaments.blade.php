<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ activeTab: 'ongoing', searchQuery: '' }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b-2 border-black px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-black">ALL TOURNAMENTS</h1>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="text-gray-600 hover:text-gray-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="flex gap-4 mb-6">
                    <a href="/manager/tournaments/create" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-8 py-3 rounded-md font-semibold transition duration-200 uppercase">
                        Create Tournament
                    </a>
                    <a href="{{ route('manager.tournaments.generate') }}" 
                       onclick="showGenerateLoading()"
                       class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-8 py-3 rounded-md font-semibold transition duration-200 uppercase relative">
                        <span id="generate-btn-text">Generate Tournament</span>
                        <span id="generate-btn-spinner" class="hidden">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generating...
                        </span>
                    </a>
                    
                    <!-- Loading Overlay -->
                    <div id="generate-loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                        <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-16 w-16 text-[#2C5F4F] mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Generating Tournament...</h3>
                                <p class="text-gray-600 text-center">Analyzing past tournaments and creating your tournament template. Please wait...</p>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        function showGenerateLoading() {
                            const overlay = document.getElementById('generate-loading-overlay');
                            const btnText = document.getElementById('generate-btn-text');
                            const btnSpinner = document.getElementById('generate-btn-spinner');
                            
                            if (overlay && btnText && btnSpinner) {
                                overlay.classList.remove('hidden');
                                btnText.classList.add('hidden');
                                btnSpinner.classList.remove('hidden');
                            }
                        }
                        // Ensure overlay/spinner are reset when returning/back
                        window.addEventListener('pageshow', () => {
                            const overlay = document.getElementById('generate-loading-overlay');
                            const btnText = document.getElementById('generate-btn-text');
                            const btnSpinner = document.getElementById('generate-btn-spinner');
                            if (overlay) overlay.classList.add('hidden');
                            if (btnText) btnText.classList.remove('hidden');
                            if (btnSpinner) btnSpinner.classList.add('hidden');
                        });
                    </script>
                </div>

                <!-- Search Bar -->
                <div class="mb-6">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" x-model="searchQuery" placeholder="Search for tournaments" class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-300 mb-6">
                    <div class="flex space-x-8">
                        <button 
                            @click="activeTab = 'ongoing'" 
                            :class="activeTab === 'ongoing' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Ongoing
                        </button>
                        <button 
                            @click="activeTab = 'upcoming'" 
                            :class="activeTab === 'upcoming' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Upcoming
                        </button>
                        <button 
                            @click="activeTab = 'completed'" 
                            :class="activeTab === 'completed' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Completed
                        </button>
                        <button 
                            @click="activeTab = 'your'" 
                            :class="activeTab === 'your' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Your Tournaments
                        </button>
                    </div>
                </div>

                <!-- Tournament Tables -->
                <!-- Ongoing Tournaments -->
                <div x-show="activeTab === 'ongoing'" x-cloak>
                    @php
                        $ongoingTournaments = $allTournaments->where('status', 'ongoing');
                        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
                        $sortLink = function($key) use ($nextDir, $sort) {
                            return request()->fullUrlWithQuery([
                                'sort' => $key,
                                'dir' => ($sort === $key ? $nextDir : 'asc'),
                            ]);
                        };
                        $arrow = function($key) use ($sort, $dir) {
                            if ($sort !== $key) return '↕';
                            return $dir === 'asc' ? '▲' : '▼';
                        };
                    @endphp
                    @if($ongoingTournaments->count() > 0)
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('name') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('start_date') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('venue') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('organizer') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Organizer <span class="text-gray-400">{{ $arrow('organizer') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($ongoingTournaments as $tournament)
                                            <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $tournament->start_date->format('M d, Y') }}</div>
                                                    <div class="text-sm text-gray-500">{{ $tournament->end_date->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-normal break-words" style="max-width: 200px;">
                                                    <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $tournament->club->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($tournament->bracket_type === 'round_robin')
                                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Round Robin</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Single Elimination</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('manager.tournaments.show', $tournament->id) }}" class="text-[#2C5F4F] hover:text-[#244D3E]">View Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow p-12 text-center">
                            <p class="text-gray-500">No ongoing tournaments found.</p>
                        </div>
                    @endif
                </div>

                <!-- Upcoming Tournaments -->
                <div x-show="activeTab === 'upcoming'" x-cloak>
                    @php
                        // Treat 'published' as upcoming so managers can see newly published events that will start soon
                        $upcomingTournaments = $allTournaments->filter(function($t) {
                            return in_array($t->status, ['upcoming', 'published']);
                        });
                    @endphp
                    @if($upcomingTournaments->count() > 0)
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        @php
                                            $nextDir = $dir === 'asc' ? 'desc' : 'asc';
                                            $sortLink = function($key) use ($nextDir, $sort) {
                                                return request()->fullUrlWithQuery([
                                                    'sort' => $key,
                                                    'dir' => ($sort === $key ? $nextDir : 'asc'),
                                                ]);
                                            };
                                            $arrow = function($key) use ($sort, $dir) {
                                                if ($sort !== $key) return '↕';
                                                return $dir === 'asc' ? '▲' : '▼';
                                            };
                                        @endphp
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('name') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('start_date') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('venue') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('organizer') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Organizer <span class="text-gray-400">{{ $arrow('organizer') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($upcomingTournaments as $tournament)
                                            <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $tournament->start_date->format('M d, Y') }}</div>
                                                    <div class="text-sm text-gray-500">{{ $tournament->end_date->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-normal break-words" style="max-width: 200px;">
                                                    <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $tournament->club->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($tournament->bracket_type === 'round_robin')
                                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Round Robin</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Single Elimination</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('manager.tournaments.show', $tournament->id) }}" class="text-[#2C5F4F] hover:text-[#244D3E]">View Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow p-12 text-center">
                            <p class="text-gray-500">No upcoming tournaments found.</p>
                        </div>
                    @endif
                </div>

                <!-- Completed Tournaments -->
                <div x-show="activeTab === 'completed'" x-cloak>
                    @php
                        $completedTournaments = $allTournaments->where('status', 'completed');
                    @endphp
                    @if($completedTournaments->count() > 0)
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        @php
                                            $nextDir = $dir === 'asc' ? 'desc' : 'asc';
                                            $sortLink = function($key) use ($nextDir, $sort) {
                                                return request()->fullUrlWithQuery([
                                                    'sort' => $key,
                                                    'dir' => ($sort === $key ? $nextDir : 'asc'),
                                                ]);
                                            };
                                            $arrow = function($key) use ($sort, $dir) {
                                                if ($sort !== $key) return '↕';
                                                return $dir === 'asc' ? '▲' : '▼';
                                            };
                                        @endphp
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('name') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('start_date') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('venue') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('organizer') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Organizer <span class="text-gray-400">{{ $arrow('organizer') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($completedTournaments as $tournament)
                                            <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150 opacity-75">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $tournament->start_date->format('M d, Y') }}</div>
                                                    <div class="text-sm text-gray-500">{{ $tournament->end_date->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-normal break-words" style="max-width: 200px;">
                                                    <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $tournament->club->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($tournament->bracket_type === 'round_robin')
                                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Round Robin</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Single Elimination</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('manager.tournaments.show', $tournament->id) }}" class="text-[#2C5F4F] hover:text-[#244D3E]">View Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow p-12 text-center">
                            <p class="text-gray-500">No completed tournaments found.</p>
                        </div>
                    @endif
                </div>

                <!-- Your Tournaments -->
                <div x-show="activeTab === 'your'" x-cloak>
                    @php
                        $nextDir = $dir === 'asc' ? 'desc' : 'asc';
                        $sortLink = function($key) use ($nextDir, $sort) {
                            return request()->fullUrlWithQuery([
                                'sort' => $key,
                                'dir' => ($sort === $key ? $nextDir : 'asc'),
                            ]);
                        };
                        $arrow = function($key) use ($sort, $dir) {
                            if ($sort !== $key) return '↕';
                            return $dir === 'asc' ? '▲' : '▼';
                        };
                    @endphp
                    @if($myTournaments->count() > 0)
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('name') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <a href="{{ $sortLink('start_date') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                                    Created <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                                </a>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($myTournaments as $tournament)
                                            <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->description ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500 max-w-md truncate">{{ $tournament->description ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                        @if($tournament->status === 'published') bg-blue-100 text-blue-700
                                                        @elseif($tournament->status === 'ongoing') bg-green-100 text-green-700
                                                        @elseif($tournament->status === 'completed') bg-purple-100 text-purple-700
                                                        @elseif($tournament->status === 'upcoming') bg-yellow-100 text-yellow-700
                                                        @else bg-red-100 text-red-700
                                                        @endif">
                                                        {{ ucfirst($tournament->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $tournament->created_at->diffForHumans() }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('manager.tournaments.show', $tournament->id) }}" class="text-[#2C5F4F] hover:text-[#244D3E]">View Details</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow p-12 text-center">
                            <p class="text-gray-500">You haven't created any tournaments yet.</p>
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</body>
</html>
