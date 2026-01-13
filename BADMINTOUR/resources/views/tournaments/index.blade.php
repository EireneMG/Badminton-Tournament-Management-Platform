<x-dashboard-layout title="Tournaments">
    <div class="max-w-7xl mx-auto" x-data="{ activeTab: '{{ request('tab', 'ongoing') }}', searchQuery: '' }">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold">ALL TOURNAMENTS</h2>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Search for tournaments"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                    id="tournamentSearch"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        @php
            $currentSort = $sort ?? request('sort', 'start_date');
            $currentDir = $dir ?? request('dir', 'asc');
            $nextDir = $currentDir === 'asc' ? 'desc' : 'asc';
            $sortLink = function($key, $tab) use ($currentSort, $currentDir, $nextDir) {
                return request()->fullUrlWithQuery([
                    'sort' => $key,
                    'dir' => ($currentSort === $key ? $nextDir : 'asc'),
                    'tab' => $tab,
                ]);
            };
            $arrow = function($key) use ($currentSort, $currentDir) {
                if ($currentSort !== $key) return '↕';
                return $currentDir === 'asc' ? '▲' : '▼';
            };
        @endphp

        <!-- Tabs -->
        <div class="border-b-2 border-[#D4A574] mb-6">
            <div class="flex space-x-8">
                <button 
                    @click="activeTab = 'ongoing'"
                    :class="activeTab === 'ongoing' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'ongoing' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    ONGOING ({{ $ongoingTournaments->count() }})
                </button>
                <button 
                    @click="activeTab = 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'upcoming' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    UPCOMING ({{ $upcomingTournaments->count() }})
                </button>
                <button 
                    @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'completed' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    COMPLETED ({{ $completedTournaments->count() }})
                </button>
                <button 
                    @click="activeTab = 'registered'"
                    :class="activeTab === 'registered' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'registered' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    MY TOURNAMENTS ({{ $myTournaments->count() }})
                </button>
            </div>
        </div>

        <!-- Ongoing Tournaments -->
        <div x-show="activeTab === 'ongoing'">
            @if($ongoingTournaments->count() > 0)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 w-52 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('name','ongoing') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('start_date','ongoing') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-64 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('venue','ongoing') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('organizer','ongoing') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Organizer <span class="text-gray-400">{{ $arrow('organizer') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($ongoingTournaments as $tournament)
                                    <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                                - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900">{{ $tournament->club->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($tournament->bracket_type === 'round_robin')
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Round Robin</span>
                                            @else
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Single Elimination</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="text-[#C85A54] hover:text-[#B54A44] font-semibold">View Tournament</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-600 font-semibold text-lg">No ongoing tournaments</p>
                    <p class="text-gray-500 text-sm mt-2">Check back later for upcoming events</p>
                </div>
            @endif
        </div>

        <!-- Upcoming Tournaments -->
        <div x-show="activeTab === 'upcoming'">
            @if($upcomingTournaments->count() > 0)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 w-52 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('name','upcoming') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('start_date','upcoming') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-64 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('venue','upcoming') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('organizer','upcoming') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Organizer <span class="text-gray-400">{{ $arrow('organizer') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($upcomingTournaments as $tournament)
                                    <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                                - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900">{{ $tournament->club->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($tournament->bracket_type === 'round_robin')
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Round Robin</span>
                                            @else
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Single Elimination</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="text-[#C85A54] hover:text-[#B54A44] font-semibold">Register Now</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-600 font-semibold text-lg">No upcoming tournaments</p>
                    <p class="text-gray-500 text-sm mt-2">New tournaments will appear here soon</p>
                </div>
            @endif
        </div>

        <!-- Completed Tournaments -->
        <div x-show="activeTab === 'completed'">
            @if($completedTournaments->count() > 0)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 w-52 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('name','completed') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('start_date','completed') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-64 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('venue','completed') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('organizer','completed') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Organizer <span class="text-gray-400">{{ $arrow('organizer') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($completedTournaments as $tournament)
                                    <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name)) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                                - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900">{{ $tournament->club->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @if($tournament->bracket_type === 'round_robin')
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Round Robin</span>
                                            @else
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Single Elimination</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="text-[#C85A54] hover:text-[#B54A44] font-semibold">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-700 font-semibold text-lg">No completed tournaments</p>
                </div>
            @endif
        </div>

        <!-- My Tournaments -->
        <div x-show="activeTab === 'registered'">
            @if($myTournaments->count() > 0)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 w-52 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('name','registered') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Tournament Name <span class="text-gray-400">{{ $arrow('name') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-44 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('start_date','registered') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Dates <span class="text-gray-400">{{ $arrow('start_date') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-64 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('venue','registered') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Venue <span class="text-gray-400">{{ $arrow('venue') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ $sortLink('status','registered') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Status <span class="text-gray-400">{{ $arrow('status') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 w-32 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($myTournaments as $tournament)
                                    <tr x-show="!searchQuery || '{{ addslashes(strtolower($tournament->name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->venue_name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->club->name ?? '')) }}'.includes(searchQuery.toLowerCase()) || '{{ addslashes(strtolower($tournament->categories->pluck('name')->join(', ') ?? '')) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm font-medium text-gray-900">{{ $tournament->name }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                                - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-normal break-words">
                                            <div class="text-sm text-gray-900 leading-snug">{{ $tournament->venue_name }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($tournament->status === 'ongoing') bg-green-100 text-green-700
                                                @elseif($tournament->status === 'upcoming') bg-blue-100 text-blue-700
                                                @else bg-gray-100 text-gray-700
                                                @endif">
                                                {{ ucfirst($tournament->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-600 font-semibold text-lg">You haven't registered for any tournaments</p>
                    <button @click="activeTab = 'upcoming'" class="mt-4 text-[#2C5F4F] hover:text-[#1B4965] font-semibold underline">
                        Browse Upcoming Tournaments
                    </button>
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
