<x-dashboard-layout title="Player Dashboard">
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-[#2C5F4F] to-[#1B4965] rounded-lg shadow p-6 text-white">
            <h1 class="text-2xl font-bold">Welcome, {{ $player->first_name }}!</h1>
            <div class="mt-2 flex items-center space-x-6 text-sm">
                @if($clubMembership)
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span>Club: {{ $clubMembership->club->name }}</span>
                </div>
                @else
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>No Club - <a href="{{ route('clubs.index') }}" class="underline hover:text-[#D4A574]">Join a club</a></span>
                </div>
                @endif
                @if($playerRanking)
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>Rating: {{ number_format($playerRanking->current_rating) }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Tournaments & Registrations -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Upcoming Tournaments -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Upcoming Tournaments</h2>
                        <a href="{{ route('tournaments.index') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View All →</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($upcomingTournaments as $tournament)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-[#D4A574] transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900">{{ $tournament->name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $tournament->venue_name }}</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <span>📅 {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}</span>
                                        <span>⏰ Registration ends: {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d') }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('tournaments.show', $tournament->id) }}" class="bg-[#2C5F4F] hover:bg-[#1B4965] text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                    View
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z"/>
                            </svg>
                            <p class="text-sm">No upcoming tournaments at the moment</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- My Registrations -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">My Tournament Registrations</h2>
                    <div class="space-y-3">
                        @forelse($playerRegistrations as $registration)
                        @php
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-700',
                                'eligible' => 'bg-blue-100 text-blue-700',
                                'awaiting_payment' => 'bg-yellow-100 text-yellow-700',
                                'paid' => 'bg-green-100 text-green-700',
                                'approved' => 'bg-[#2C5F4F] text-white',
                                'rejected' => 'bg-red-100 text-red-700',
                                'withdrawn' => 'bg-gray-100 text-gray-700'
                            ];
                            $statusColor = $statusColors[$registration->status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $registration->tournament->name }}</h3>
                                <p class="text-xs text-gray-600 mt-1">{{ $registration->category->full_name ?? 'General' }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-6 text-gray-500">
                            <p class="text-sm">No tournament registrations yet</p>
                            <a href="{{ route('tournaments.index') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold mt-2 inline-block">Browse Tournaments</a>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right Column - Rankings -->
            <div class="space-y-6">
                <!-- Top Players -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Top Players</h2>
                        <a href="{{ route('ranking.index') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View All →</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($topPlayers as $index => $rating)
                        <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg transition">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-[#D4A574]' : 'bg-gray-400' }} text-white font-bold text-sm">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $rating->player->first_name }} {{ $rating->player->last_name }}</p>
                                <p class="text-xs text-gray-600 truncate">{{ $rating->player->approvedClubMembership->club->name ?? 'No Club' }}</p>
                            </div>
                            <span class="font-bold text-[#2C5F4F] text-sm">{{ number_format($rating->current_rating) }}</span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-sm py-4">No rankings yet</p>
                        @endforelse
                    </div>
                </div>

                <!-- Top Clubs -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Top Clubs</h2>
                        <a href="{{ route('clubs.index') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View All →</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($topClubs as $club)
                        <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:border-[#D4A574] transition">
                            @if($club->logo_path)
                            <img src="{{ asset('storage/' . $club->logo_path) }}" alt="{{ $club->name }}" class="w-12 h-12 rounded-full object-cover">
                            @else
                            <div class="w-12 h-12 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($club->name, 0, 1)) }}
                            </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $club->name }}</p>
                                <p class="text-xs text-gray-600">{{ $club->approved_players_count }} members</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-sm py-4">No clubs yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
