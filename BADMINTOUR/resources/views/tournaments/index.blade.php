<x-dashboard-layout title="Tournaments">
    <div class="max-w-7xl mx-auto" x-data="{ activeTab: 'ongoing' }">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold">ALL TOURNAMENTS</h2>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input 
                    type="text" 
                    placeholder="Search for tournaments"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                    id="tournamentSearch"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

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
        <div x-show="activeTab === 'ongoing'" class="space-y-4">
            @forelse($ongoingTournaments as $tournament)
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                <h3 class="text-xl font-bold mb-3">{{ $tournament->name }}</h3>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $tournament->venue_name }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Organized by {{ $tournament->club->name }}</span>
                    </div>
                    @if($tournament->categories->count() > 0)
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Categories: {{ $tournament->categories->pluck('name')->join(', ') }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="flex justify-end">
                    <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="bg-[#C85A54] text-white px-6 py-2 rounded-md hover:bg-[#B54A44] transition font-semibold">
                        View Tournament
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white border-2 border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z"/>
                </svg>
                <p class="text-gray-600 font-semibold text-lg">No ongoing tournaments</p>
                <p class="text-gray-500 text-sm mt-2">Check back later for upcoming events</p>
            </div>
            @endforelse
        </div>

        <!-- Upcoming Tournaments -->
        <div x-show="activeTab === 'upcoming'" class="space-y-4">
            @forelse($upcomingTournaments as $tournament)
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                <h3 class="text-xl font-bold mb-3">{{ $tournament->name }}</h3>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Registration ends: {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $tournament->venue_name }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Organized by {{ $tournament->club->name }}</span>
                    </div>
                    @if($tournament->categories->count() > 0)
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Categories: {{ $tournament->categories->pluck('name')->join(', ') }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="flex justify-end">
                    <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="bg-[#C85A54] text-white px-6 py-2 rounded-md hover:bg-[#B54A44] transition font-semibold">
                        Register Now
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white border-2 border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-600 font-semibold text-lg">No upcoming tournaments</p>
                <p class="text-gray-500 text-sm mt-2">New tournaments will appear here soon</p>
            </div>
            @endforelse
        </div>

        <!-- Completed Tournaments -->
        <div x-show="activeTab === 'completed'" class="space-y-4">
            @forelse($completedTournaments as $tournament)
            <div class="bg-white border-2 border-gray-300 rounded-lg p-6 opacity-75">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold mb-3">{{ $tournament->name }}</h3>
                        
                        <div class="space-y-2">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $tournament->venue_name }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-semibold">Completed</span>
                </div>
            </div>
            @empty
            <div class="bg-white border-2 border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                <p class="text-gray-600 font-semibold text-lg">No completed tournaments</p>
            </div>
            @endforelse
        </div>

        <!-- My Tournaments -->
        <div x-show="activeTab === 'registered'" class="space-y-4">
            @forelse($myTournaments as $tournament)
            <div class="bg-white border-2 border-[#2C5F4F] rounded-lg p-6">
                <h3 class="text-xl font-bold mb-3">{{ $tournament->name }}</h3>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $tournament->venue_name }}</span>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="bg-[#2C5F4F] text-white px-6 py-2 rounded-md hover:bg-[#1B4965] transition font-semibold">
                        View Details
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white border-2 border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <p class="text-gray-600 font-semibold text-lg">You haven't registered for any tournaments</p>
                <button @click="activeTab = 'upcoming'" class="mt-4 text-[#2C5F4F] hover:text-[#1B4965] font-semibold underline">
                    Browse Upcoming Tournaments
                </button>
            </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
