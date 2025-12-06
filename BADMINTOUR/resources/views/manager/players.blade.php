<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players List - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <div class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-[#2C5F4F]">PLAYERS LIST</h1>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8">
                <!-- Search Bar -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="searchInput"
                            placeholder="Search players by name, email, or club" 
                            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent"
                            onkeyup="filterPlayers()"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Players List -->
                <div class="bg-white rounded-lg shadow overflow-hidden" id="playersList">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ELO Rating</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($allPlayers as $player)
                        @php
                            $playerClubMembership = $player->approvedClubMembership;
                            $playerClub = $playerClubMembership?->club;
                            
                            // Get player's ELO rating for display
                            $playerElo = \App\Models\EloRating::where('player_id', $player->id)
                                ->where('category', 'MS')
                                ->first();
                            $displayElo = $playerElo ? number_format($playerElo->current_rating) : ($playerClubMembership?->provisional_elo ? number_format($playerClubMembership->provisional_elo) : 'N/A');
                            
                            // Get ranking position
                            $rankingService = app(\App\Services\RankingService::class);
                            $rankingPosition = $rankingService->getPlayerRanking($player, 'MS');
                            
                            // Check if player has any approved club membership
                            $hasClubMembership = $player->clubMemberships()
                                ->where('status', 'approved')
                                ->exists();
                            
                            // Check if player is already in this manager's club
                            $isInManagerClub = $club && $player->clubMemberships()
                                ->where('club_id', $club->id)
                                ->where('status', 'approved')
                                ->exists();
                            
                            // Check if player has pending request to this manager's club
                            $hasPendingRequest = $club && $player->clubMemberships()
                                ->where('club_id', $club->id)
                                ->where('status', 'pending')
                                ->exists();
                            
                            // Only show invite button if:
                            // 1. Manager has a club
                            // 2. Player has NO club affiliation (no approved membership anywhere)
                            // 3. Player is NOT already in this manager's club
                            // 4. Player does NOT have a pending request to this manager's club
                            $canInvite = $club && !$hasClubMembership && !$isInManagerClub && !$hasPendingRequest;
                        @endphp
                        <tr class="player-card hover:bg-gray-50 transition duration-150" data-name="{{ strtolower($player->first_name . ' ' . $player->last_name) }}" data-email="{{ strtolower($player->email) }}" data-club="{{ strtolower($playerClub?->name ?? 'no club') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($player->profile_photo)
                                        <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->first_name }}" class="h-10 w-10 rounded-full object-cover border-2 border-[#D4A574] mr-3">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-bold text-sm border-2 border-[#D4A574] mr-3">
                                            {{ strtoupper(substr($player->first_name, 0, 1) . substr($player->last_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $player->first_name }} {{ $player->last_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $player->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $playerClub->name ?? 'No Club' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ ucfirst($player->gender ?? 'N/A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($displayElo !== 'N/A')
                                    <div class="text-sm font-semibold text-gray-900">{{ $displayElo }}</div>
                                @else
                                    <div class="text-sm text-gray-500">N/A</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($rankingPosition)
                                    <div class="text-sm font-semibold text-[#2C5F4F]">#{{ $rankingPosition }}</div>
                                @else
                                    <div class="text-sm text-gray-500">N/A</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    @if($canInvite)
                                        <form action="{{ route('manager.club.invite') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="player_email" value="{{ $player->email }}">
                                            <button type="submit" class="bg-[#D4A574] hover:bg-[#C49564] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                                Invite
                                            </button>
                                        </form>
                                    @elseif($hasPendingRequest)
                                        <span class="text-xs text-gray-500 italic">Pending</span>
                                    @elseif($hasClubMembership)
                                        <span class="text-xs text-gray-500 italic">In Club</span>
                                    @endif
                                    <a href="{{ route('players.show', $player) }}" class="text-[#2C5F4F] hover:text-[#244D3E]">View Profile</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <h3 class="text-xl font-bold text-gray-700 mb-2">No Players Found</h3>
                                <p class="text-gray-500">There are no registered players in the system yet.</p>
                            </td>
                        </tr>
                    @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterPlayers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('.player-card');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const club = row.getAttribute('data-club') || '';
                
                if (name.includes(filter) || email.includes(filter) || club.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
