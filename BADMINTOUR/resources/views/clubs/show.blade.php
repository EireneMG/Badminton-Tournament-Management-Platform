<x-dashboard-layout title="Club Profile">
    <div class="max-w-5xl mx-auto">
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
        <div class="bg-white rounded-lg p-6 mb-6 border-b-4 border-[#D4A574]">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    @if($club->logo)
                        <img src="{{ asset('storage/' . $club->logo) }}" alt="{{ $club->name }}" class="w-24 h-24 rounded-full object-cover mr-6">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center mr-6">
                            <span class="text-3xl font-bold text-gray-400">{{ substr($club->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold">{{ $club->name }}</h1>
                        <p class="text-gray-600">{{ $club->city }}, {{ $club->province }}</p>
                    </div>
                </div>
                
                @if($canJoin)
                    <form action="{{ route('clubs.join', $club) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
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

            <div class="grid grid-cols-2 gap-6">
                <!-- Description -->
                <div>
                    <h3 class="text-lg font-bold mb-3">About</h3>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 bg-gray-50">
                        <p class="text-gray-600">{{ $club->description ?? 'No description provided.' }}</p>
                    </div>
                </div>

                <!-- Members Count -->
                <div>
                    <h3 class="text-lg font-bold mb-3"># Members</h3>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 bg-gray-50 flex items-center justify-center">
                        <span class="text-5xl font-bold">{{ $memberCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mt-6 grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-bold mb-3">Contact Email</h3>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 bg-gray-50">
                        <p class="text-gray-600">{{ $club->contact_email ?? 'Not provided' }}</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-3">Contact Phone</h3>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 bg-gray-50">
                        <p class="text-gray-600">{{ $club->contact_phone ?? 'Not provided' }}</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Tournaments -->
            @if($club->tournaments->count() > 0)
                <div class="mt-6">
                    <h3 class="text-lg font-bold mb-3">Upcoming Tournaments</h3>
                    <div class="space-y-2">
                        @foreach($club->tournaments->take(3) as $tournament)
                            <a href="{{ route('player.tournaments.show', $tournament) }}" class="flex items-center justify-between p-3 border-2 border-[#D4A574] rounded-lg hover:bg-gray-50 transition">
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-[#C85A54] rounded-full mr-3"></span>
                                    <span class="font-semibold">{{ $tournament->name }}</span>
                                </div>
                                <span class="text-gray-600">{{ $tournament->start_date->format('M d, Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Members List -->
        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
            <h3 class="text-lg font-bold mb-4">Members</h3>
            
            <div class="space-y-3">
                @forelse($club->approvedPlayers as $member)
                    @php
                        // Get player's ELO rating for display
                        $playerElo = \App\Models\EloRating::where('player_id', $member->id)
                            ->where('category', $member->gender === 'Female' ? 'WS' : 'MS')
                            ->first();
                        $clubMembership = \App\Models\ClubPlayer::where('player_id', $member->id)
                            ->where('club_id', $club->id)
                            ->where('status', 'approved')
                            ->first();
                        $displayElo = $playerElo ? $playerElo->current_rating : ($clubMembership->provisional_elo ?? 'N/A');
                        
                        // Calculate ranking position
                        $rankingService = app(\App\Services\RankingService::class);
                        $category = $member->gender === 'Female' ? 'WS' : 'MS';
                        $playerRanking = $rankingService->getPlayerRanking($member, $category);
                    @endphp
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                                <span class="text-lg font-bold text-gray-400">{{ substr($member->first_name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-sm text-gray-600">{{ $member->gender }}</p>
                                @if($displayElo !== 'N/A')
                                    <p class="text-xs text-gray-500">ELO: {{ $displayElo }}</p>
                                @endif
                                @if($playerRanking)
                                    <p class="text-xs font-semibold text-[#2C5F4F]">Rank: #{{ $playerRanking }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-600">
                            <p>Joined {{ $member->pivot->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <p>No approved members yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-dashboard-layout>
