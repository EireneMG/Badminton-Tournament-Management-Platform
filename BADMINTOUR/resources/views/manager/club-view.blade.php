<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $club->name }} - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ showStatisticsModal: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('manager.clubs') }}" class="text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-semibold text-[#2C5F4F]">{{ $club->name }}</h1>
                            <p class="text-sm text-gray-500">Viewing club details (read-only)</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <div class="max-w-7xl mx-auto">
                    <!-- Club Overview -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 mb-6">
                        <div class="flex items-start gap-4 mb-6">
                            <!-- Club Logo -->
                            @if($club->logo)
                                <img src="{{ Storage::url($club->logo) }}" alt="{{ $club->name }} Logo" class="w-24 h-24 rounded-lg object-cover border-2 border-[#D4A574] shadow-sm">
                            @else
                                <div class="w-24 h-24 rounded-lg bg-[#2C5F4F] flex items-center justify-center border-2 border-[#D4A574] shadow-sm">
                                    <span class="text-white text-3xl font-bold">{{ strtoupper(substr($club->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <h2 class="text-3xl font-semibold text-[#2C5F4F]">{{ $club->name }}</h2>
                                    <button @click="showStatisticsModal = true" class="bg-[#C85A54] hover:bg-[#B04A44] text-white px-4 py-2 rounded-md shadow-sm transition duration-200 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        View Statistics
                                    </button>
                                </div>
                                <div class="space-y-2 text-gray-600">
                                    <p class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>{{ $club->city }}, {{ $club->province }}</span>
                                    </p>
                                    <p class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span>Manager: {{ $club->manager->first_name }} {{ $club->manager->last_name }}</span>
                                    </p>
                                    <p class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span><strong>{{ $club->approvedPlayers->count() }}</strong> Members</span>
                                    </p>
                                    @if($club->contact_email)
                                    <p class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ $club->contact_email }}</span>
                                    </p>
                                    @endif
                                    @if($club->contact_phone)
                                    <p class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span>{{ $club->contact_phone }}</span>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Club Description -->
                        @if($club->description)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-[#2C5F4F] mb-3">About</h3>
                                <p class="text-gray-700 leading-relaxed">{{ $club->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Members List (Read-Only) -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-[#2C5F4F]">Club Members <span class="text-sm font-normal text-gray-500">({{ $approvedPlayers->count() }})</span></h2>
                        </div>
                        
                        @if($approvedPlayers->count() > 0)
                            <div class="overflow-x-auto px-6 py-4">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skill Level</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ELO Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($approvedPlayers as $clubPlayer)
                                            @php
                                                $player = $clubPlayer->player;
                                                $playerElo = \App\Models\EloRating::where('player_id', $player->id)->where('category', 'MS')->first();
                                                $displayElo = $playerElo ? $playerElo->current_rating : ($clubPlayer->provisional_elo ?? 'N/A');
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        @if($player->profile_photo)
                                                            <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->name }}" class="h-10 w-10 rounded-full object-cover border-2 border-[#D4A574] mr-3">
                                                        @else
                                                            <div class="h-10 w-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-semibold border-2 border-[#D4A574] mr-3">
                                                                {{ strtoupper(substr($player->first_name, 0, 1) . substr($player->last_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div class="text-sm font-medium text-gray-900">{{ $player->first_name }} {{ $player->last_name }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $player->email }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if($clubPlayer->skill_level)
                                                            Level {{ $clubPlayer->skill_level }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 font-semibold">
                                                        @if($displayElo !== 'N/A')
                                                            {{ number_format($displayElo) }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-6 py-8 text-center text-gray-500">
                                <p>This club has no members yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div x-show="showStatisticsModal" x-cloak style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4" @click.self="showStatisticsModal = false" @keydown.escape.window="showStatisticsModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click.stop x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                <h2 class="text-2xl font-bold text-[#2C5F4F]">Club Statistics - {{ $club->name }}</h2>
                <button @click="showStatisticsModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                @php
                    $club->load('tournaments');
                    $totalMatches = \App\Models\TournamentMatch::whereIn('tournament_id', $club->tournaments->pluck('id'))->count();
                    $completedMatches = \App\Models\TournamentMatch::whereIn('tournament_id', $club->tournaments->pluck('id'))
                        ->where('status', \App\Enums\MatchStatus::COMPLETED->value)
                        ->count();
                    $memberEloRatings = \App\Models\EloRating::whereIn('player_id', $approvedPlayers->pluck('player.id'))
                        ->get();
                    $averageElo = $memberEloRatings->count() > 0 
                        ? round($memberEloRatings->avg('current_rating'), 0) 
                        : 0;
                    $tournamentsByStatus = [
                        'published' => $club->tournaments->where('status', \App\Enums\TournamentStatus::PUBLISHED->value)->count(),
                        'upcoming' => $club->tournaments->where('status', \App\Enums\TournamentStatus::UPCOMING->value)->count(),
                        'ongoing' => $club->tournaments->where('status', \App\Enums\TournamentStatus::ONGOING->value)->count(),
                        'completed' => $club->tournaments->where('status', \App\Enums\TournamentStatus::COMPLETED->value)->count(),
                    ];
                @endphp
                <div class="space-y-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">TOTAL MEMBERS</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ $approvedPlayers->count() }}</p>
                        </div>
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">TOURNAMENTS HOSTED</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ $club->tournaments->count() }}</p>
                        </div>
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">TOTAL MATCHES</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ $totalMatches }}</p>
                        </div>
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">AVG MEMBER ELO</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ number_format($averageElo) }}</p>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-gray-200">
                        <p class="text-sm font-semibold text-gray-700 mb-4">Tournaments by Status</p>
                        <div class="grid grid-cols-4 gap-3">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Published</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $tournamentsByStatus['published'] }}</p>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Upcoming</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $tournamentsByStatus['upcoming'] }}</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Ongoing</p>
                                <p class="text-2xl font-bold text-green-600">{{ $tournamentsByStatus['ongoing'] }}</p>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Completed</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $tournamentsByStatus['completed'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

