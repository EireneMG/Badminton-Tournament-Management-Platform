<x-dashboard-layout title="My Matches">
    <div class="max-w-7xl mx-auto" x-data="{ activeTab: 'upcoming' }">
        <!-- Tabs -->
        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-1 mb-6 inline-flex space-x-1">
            <button @click="activeTab = 'upcoming'" :class="activeTab === 'upcoming' ? 'bg-[#C85A54] text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" class="px-6 py-2 rounded font-semibold transition">
                UPCOMING ({{ $upcomingMatches->count() }})
            </button>
            <button @click="activeTab = 'live'" :class="activeTab === 'live' ? 'bg-[#C85A54] text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" class="px-6 py-2 rounded font-semibold transition">
                LIVE ({{ $liveMatches->count() }})
            </button>
            <button @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'bg-[#C85A54] text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" class="px-6 py-2 rounded font-semibold transition">
                COMPLETED ({{ $completedMatches->count() }})
            </button>
        </div>

        <!-- Upcoming Matches -->
        <div x-show="activeTab === 'upcoming'" class="space-y-4">
            @forelse($upcomingMatches as $match)
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold">{{ $match->tournament?->name ?? 'N/A' }}</h3>
                            <p class="text-gray-600">{{ $match->category->name }} - Round {{ $match->round }}</p>
                        </div>
                        <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg font-semibold">
                            {{ strtoupper($match->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 items-center">
                        <!-- Player 1 -->
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2">
                                <span class="text-2xl font-bold text-gray-400">{{ substr($match->player1->first_name, 0, 1) }}</span>
                            </div>
                            <p class="font-semibold">{{ $match->player1->first_name }} {{ $match->player1->last_name }}</p>
                            <p class="text-sm text-gray-600">{{ $match->player1->approvedClubMembership?->club->name ?? 'No Club' }}</p>
                        </div>

                        <!-- VS -->
                        <div class="text-center">
                            <span class="text-2xl font-bold text-gray-400">VS</span>
                        </div>

                        <!-- Player 2 -->
                        <div class="text-center">
                            @if($match->player2)
                                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl font-bold text-gray-400">{{ substr($match->player2->first_name, 0, 1) }}</span>
                                </div>
                                <p class="font-semibold">{{ $match->player2->first_name }} {{ $match->player2->last_name }}</p>
                                <p class="text-sm text-gray-600">{{ $match->player2->approvedClubMembership?->club->name ?? 'No Club' }}</p>
                            @else
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-sm text-gray-400">TBD</span>
                                </div>
                                <p class="text-gray-400">To Be Determined</p>
                            @endif
                        </div>
                    </div>

                    @if($match->scheduled_date && $match->scheduled_time)
                        <div class="mt-4 pt-4 border-t text-center">
                            <p class="text-sm text-gray-600">Scheduled: <span class="font-semibold">
                                {{ \Carbon\Carbon::parse($match->scheduled_date)->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($match->scheduled_time)->format('h:i A') }}
                            </span></p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-600">No Upcoming Matches</p>
                    <p class="text-sm text-gray-400 mt-2">You don't have any scheduled matches at the moment.</p>
                </div>
            @endforelse
        </div>

        <!-- Live Matches -->
        <div x-show="activeTab === 'live'" x-cloak class="space-y-4">
            @forelse($liveMatches as $match)
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold">{{ $match->tournament?->name ?? 'N/A' }}</h3>
                            <p class="text-gray-600">{{ $match->category->name }} - Round {{ $match->round }}</p>
                        </div>
                        <span class="px-4 py-2 bg-red-100 text-red-800 rounded-lg font-semibold animate-pulse">
                            LIVE
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 items-center">
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2">
                                <span class="text-2xl font-bold text-gray-400">{{ substr($match->player1->first_name, 0, 1) }}</span>
                            </div>
                            <p class="font-semibold">{{ $match->player1->first_name }} {{ $match->player1->last_name }}</p>
                        </div>

                        <div class="text-center">
                            <span class="text-2xl font-bold text-gray-400">VS</span>
                        </div>

                        <div class="text-center">
                            @if($match->player2)
                                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl font-bold text-gray-400">{{ substr($match->player2->first_name, 0, 1) }}</span>
                                </div>
                                <p class="font-semibold">{{ $match->player2->first_name }} {{ $match->player2->last_name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-600">No Live Matches</p>
                    <p class="text-sm text-gray-400 mt-2">There are no matches currently in progress.</p>
                </div>
            @endforelse
        </div>

        <!-- Completed Matches -->
        <div x-show="activeTab === 'completed'" x-cloak class="space-y-4">
            @forelse($completedMatches as $match)
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold">{{ $match->tournament?->name ?? 'N/A' }}</h3>
                            <p class="text-gray-600">{{ $match->category->name }} - Round {{ $match->round }}</p>
                            <p class="text-sm text-gray-500">{{ $match->updated_at->format('M d, Y') }}</p>
                        </div>
                        <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg font-semibold">
                            COMPLETED
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 items-center">
                        <!-- Player 1 -->
                        <div class="text-center">
                            @php
                                $isPlayer1Winner = $match->result && $match->result->winner_id === $match->player1_id;
                            @endphp
                            <div class="w-16 h-16 rounded-full {{ $isPlayer1Winner ? 'bg-green-100' : 'bg-gray-200' }} flex items-center justify-center mx-auto mb-2">
                                <span class="text-2xl font-bold {{ $isPlayer1Winner ? 'text-green-600' : 'text-gray-400' }}">{{ substr($match->player1->first_name, 0, 1) }}</span>
                            </div>
                            <p class="font-semibold {{ $isPlayer1Winner ? 'text-green-600' : '' }}">{{ $match->player1->first_name }} {{ $match->player1->last_name }}</p>
                            @if($isPlayer1Winner)
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-semibold">WINNER</span>
                            @endif
                        </div>

                        <!-- Score -->
                        <div class="text-center">
                            @if($match->result)
                                @php
                                    $matchScoreService = app(\App\Services\MatchScoreService::class);
                                    $finalScore = $matchScoreService->calculateFinalScore($match->result);
                                    $setScores = $matchScoreService->getFormattedSetScores($match->result);
                                @endphp
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-gray-900">Final: {{ $finalScore['final_score'] }}</p>
                                    @foreach($setScores as $set => $score)
                                        <p class="text-xs text-gray-600">{{ ucfirst(str_replace('set', 'Set ', $set)) }}: {{ $score }}</p>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">No score</span>
                            @endif
                        </div>

                        <!-- Player 2 -->
                        <div class="text-center">
                            @if($match->player2)
                                @php
                                    $isPlayer2Winner = $match->result && $match->result->winner_id === $match->player2_id;
                                @endphp
                                <div class="w-16 h-16 rounded-full {{ $isPlayer2Winner ? 'bg-green-100' : 'bg-gray-200' }} flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl font-bold {{ $isPlayer2Winner ? 'text-green-600' : 'text-gray-400' }}">{{ substr($match->player2->first_name, 0, 1) }}</span>
                                </div>
                                <p class="font-semibold {{ $isPlayer2Winner ? 'text-green-600' : '' }}">{{ $match->player2->first_name }} {{ $match->player2->last_name }}</p>
                                @if($isPlayer2Winner)
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-semibold">WINNER</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-semibold text-gray-600">No Completed Matches</p>
                    <p class="text-sm text-gray-400 mt-2">You don't have any match history yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
