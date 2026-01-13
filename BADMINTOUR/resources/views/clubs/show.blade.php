<x-dashboard-layout title="Club Profile">
    <div class="max-w-5xl mx-auto" x-data="{ showStatisticsModal: false }">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('clubs.index') }}" class="inline-flex items-center text-gray-700 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold text-lg">Back to Clubs</span>
            </a>
        </div>

        <!-- Club Header -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-[#2C5F4F] to-[#1B4965] p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if($club->logo)
                            <img src="{{ asset('storage/' . $club->logo) }}" alt="{{ $club->name }}" class="w-20 h-20 rounded-lg object-cover border-2 border-white shadow-lg">
                        @else
                            <div class="w-20 h-20 rounded-lg bg-white bg-opacity-20 flex items-center justify-center border-2 border-white shadow-lg">
                                <span class="text-white text-3xl font-bold">{{ strtoupper(substr($club->name, 0, 2)) }}</span>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-3xl font-bold text-white">{{ $club->name }}</h1>
                            <p class="text-white text-opacity-90 mt-1">{{ $club->city }}, {{ $club->province }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button @click="showStatisticsModal = true" class="bg-[#C85A54] hover:bg-[#B04A44] text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            View Statistics
                        </button>
                        @if($canJoin)
                            <form action="{{ route('clubs.join', $club) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-white text-[#2C5F4F] px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-200">
                                    Join Club
                                </button>
                            </form>
                        @elseif($isPending)
                            <button disabled class="bg-yellow-100 text-yellow-800 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                                Request Pending
                            </button>
                        @elseif($isApproved)
                            <span class="bg-green-100 text-green-800 px-6 py-3 rounded-lg font-semibold">
                                Your Club
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $isDemoClub = $club->description && stripos($club->description, 'TEST/DEMO') !== false;
            @endphp

            @if($isDemoClub)
            <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-7a1 1 0 00-1 1v3a1 1 0 002 0V7a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold">Demo / Test Club</p>
                        <p>All join requests and tournament interactions here are for demonstration only and will not be processed. There is no real club manager assigned.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Club Information -->
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- Description -->
                    <div>
                        <h3 class="text-lg font-bold text-[#2C5F4F] mb-3">About</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-700">{{ $club->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>

                    <!-- Club Stats -->
                    <div>
                        <h3 class="text-lg font-bold text-[#2C5F4F] mb-3">Club Information</h3>
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span><strong>{{ $memberCount }}</strong> Members</span>
                            </div>
                            @if($club->contact_email)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ $club->contact_email }}</span>
                            </div>
                            @endif
                            @if($club->contact_phone)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span>{{ $club->contact_phone }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tournaments -->
                @if($club->tournaments->count() > 0)
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-bold text-[#2C5F4F] mb-3">Upcoming Tournaments</h3>
                        <div class="space-y-2">
                            @foreach($club->tournaments->take(3) as $tournament)
                                <a href="{{ route('tournaments.show', $tournament) }}" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex items-center">
                                        <span class="w-2 h-2 bg-[#C85A54] rounded-full mr-3"></span>
                                        <div>
                                            <span class="font-semibold text-gray-900">{{ $tournament->name }}</span>
                                            <p class="text-sm text-gray-600 mt-1">{{ $tournament->start_date->format('F d, Y') }} - {{ $tournament->end_date->format('F d, Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-[#2C5F4F] text-white rounded-lg text-xs font-semibold">
                                        {{ ucfirst($tournament->status) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Members List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-[#2C5F4F]">Members ({{ $memberCount }})</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ELO Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($approvedPlayers as $member)
                            @php
                                $category = $member->gender === 'Female' ? 'WS' : 'MS';
                                
                                // Get player's ELO rating for display
                                $playerElo = \App\Models\EloRating::where('player_id', $member->id)
                                    ->where('category', $category)
                                    ->first();
                                $clubMembership = \App\Models\ClubPlayer::where('player_id', $member->id)
                                    ->where('club_id', $club->id)
                                    ->where('status', 'approved')
                                    ->first();
                                $displayElo = $playerElo ? $playerElo->current_rating : ($clubMembership->provisional_elo ?? 'N/A');
                                
                                // Check if player has official ranking
                                $hasOfficialRanking = $playerElo && $playerElo->matches_played > 0;
                                // Provisional: has ELO but no matches played, OR has provisional_elo but no ELO rating yet
                                $isProvisional = ($playerElo && $playerElo->matches_played === 0) || (!$playerElo && $clubMembership && $clubMembership->provisional_elo);
                                
                                // Get ranking position (only for official rankings)
                                $playerRanking = null;
                                if ($hasOfficialRanking) {
                                    $rankingService = app(\App\Services\RankingService::class);
                                    $playerRanking = $rankingService->getPlayerRanking($member, $category);
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($member->profile_photo)
                                            <img src="{{ asset('storage/' . $member->profile_photo) }}" alt="{{ $member->first_name }}" class="h-10 w-10 rounded-full object-cover border-2 border-[#D4A574] mr-3">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-semibold border-2 border-[#D4A574] mr-3">
                                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $member->first_name }} {{ $member->last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ ucfirst($member->gender ?? 'N/A') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($displayElo !== 'N/A')
                                        <div class="text-sm text-gray-900 font-semibold">{{ number_format($displayElo) }}</div>
                                    @else
                                        <div class="text-sm text-gray-500">N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($hasOfficialRanking && $playerRanking)
                                        <div class="text-sm font-semibold text-[#2C5F4F]">#{{ $playerRanking }}</div>
                                    @elseif($isProvisional)
                                        <div class="text-sm text-gray-500 italic">Provisional - No Ranking</div>
                                    @else
                                        <div class="text-sm text-gray-500">N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $member->pivot->created_at->format('M d, Y') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No approved members yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">TOTAL MEMBERS</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ $memberCount }}</p>
                        </div>
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">TOURNAMENTS HOSTED</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ $club->tournaments->count() }}</p>
                        </div>
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">UPCOMING TOURNAMENTS</p>
                            <p class="text-2xl font-bold text-[#2C5F4F]">{{ $club->tournaments->where('start_date', '>=', now())->count() }}</p>
                        </div>
                        <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                            <p class="text-xs text-gray-600 mb-1">CLUB LOCATION</p>
                            <p class="text-lg font-bold text-[#2C5F4F]">{{ $club->city }}, {{ $club->province }}</p>
                        </div>
                    </div>
                    @if($club->tournaments->count() > 0)
                    <div class="pt-6 border-t border-gray-200">
                        <p class="text-sm font-semibold text-gray-700 mb-4">Recent Tournaments</p>
                        <div class="space-y-2">
                            @foreach($club->tournaments->take(5) as $tournament)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $tournament->name }}</p>
                                    <p class="text-xs text-gray-600">{{ $tournament->start_date->format('M d, Y') }} - {{ $tournament->end_date->format('M d, Y') }}</p>
                                </div>
                                <span class="px-3 py-1 bg-[#2C5F4F] text-white rounded-lg text-xs font-semibold">
                                    {{ ucfirst($tournament->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
