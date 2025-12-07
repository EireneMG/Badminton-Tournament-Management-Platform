<x-dashboard-layout title="Players">
    <div class="max-w-7xl mx-auto">
        <!-- Search Bar -->
        <div class="mb-8">
            <div class="relative">
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Search players by name, email, or club"
                    class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                    onkeyup="filterPlayers()"
                >
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Players List -->
        <div class="space-y-4" id="playersList">
            @forelse($allPlayers as $player)
                @php
                    $clubMembership = $player->approvedClubMembership;
                    $club = $clubMembership?->club;
                    
                    // Get player's ELO rating for display
                    $playerElo = \App\Models\EloRating::where('player_id', $player->id)
                        ->where('category', 'MS')
                        ->first();
                    $displayElo = $playerElo ? number_format($playerElo->current_rating) : ($clubMembership?->provisional_elo ? number_format($clubMembership->provisional_elo) : 'N/A');
                    
                    // Get ranking position
                    $rankingService = app(\App\Services\RankingService::class);
                    $rankingPosition = $rankingService->getPlayerRanking($player, 'MS');
                @endphp
                <div class="player-card bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition" data-name="{{ strtolower($player->first_name . ' ' . $player->last_name) }}" data-email="{{ strtolower($player->email) }}" data-club="{{ strtolower($club?->name ?? 'no club') }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Player Avatar -->
                            @if($player->profile_photo)
                                <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->first_name }}" class="h-16 w-16 rounded-full object-cover border-2 border-[#D4A574]">
                            @else
                                <div class="h-16 w-16 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-bold text-lg border-2 border-[#D4A574]">
                                    {{ strtoupper(substr($player->first_name, 0, 1) . substr($player->last_name, 0, 1)) }}
                                </div>
                            @endif
                            
                            <!-- Player Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-bold text-gray-900">{{ strtoupper($player->last_name) }}</h3>
                                    <span class="text-lg text-gray-700">{{ $player->first_name }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $player->email }}
                                    </span>
                                    @if($club)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            {{ $club->name }}
                                        </span>
                                    @endif
                                    @if($player->gender)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ ucfirst($player->gender) }}
                                        </span>
                                    @endif
                                </div>
                                @if($displayElo !== 'N/A' || $rankingPosition)
                                    <div class="mt-2 flex items-center gap-3 text-sm">
                                        @if($displayElo !== 'N/A')
                                            <span class="text-gray-700 font-semibold">ELO: {{ $displayElo }}</span>
                                        @endif
                                        @if($rankingPosition)
                                            <span class="text-[#2C5F4F] font-semibold">Rank: #{{ $rankingPosition }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

    </script>
</x-dashboard-layout>