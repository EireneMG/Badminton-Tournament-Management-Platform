<x-dashboard-layout title="Clubs">
    <div class="max-w-7xl mx-auto">
        <!-- Your Club Section -->
        @if($myClub)
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">YOUR CLUB</h2>
                <a href="{{ route('clubs.show', $myClub) }}" class="block bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                    <div class="flex items-center">
                        @if($myClub->logo)
                            <img src="{{ asset('storage/' . $myClub->logo) }}" alt="{{ $myClub->name }}" class="w-20 h-20 rounded-full object-cover mr-6">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center mr-6">
                                <span class="text-2xl font-bold text-gray-400">{{ substr($myClub->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-xl font-bold">{{ $myClub->name }}</h3>
                            <p class="text-gray-600">{{ $myClub->city }}, {{ $myClub->province }}</p>
                            <p class="text-gray-600">{{ $myClub->approved_players_count ?? $myClub->approvedPlayers->count() }} members</p>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        <!-- Pending Requests -->
        @if($pendingRequests->count() > 0)
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">PENDING REQUESTS</h2>
                <div class="space-y-4">
                    @foreach($pendingRequests as $request)
                        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if($request->club->logo)
                                        <img src="{{ asset('storage/' . $request->club->logo) }}" alt="{{ $request->club->name }}" class="w-16 h-16 rounded-full object-cover mr-4">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                                            <span class="text-xl font-bold text-gray-400">{{ substr($request->club->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="text-lg font-bold">{{ $request->club->name }}</h3>
                                        <p class="text-sm text-gray-600">Request sent {{ $request->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg font-semibold">PENDING</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- All Clubs Section -->
        <div>
            <h2 class="text-2xl font-bold mb-4">ALL CLUBS</h2>
            
            <div class="space-y-4">
                @forelse($allClubs as $club)
                    <a href="{{ route('clubs.show', $club) }}" class="block bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                        <div class="flex items-center">
                            @if($club->logo)
                                <img src="{{ asset('storage/' . $club->logo) }}" alt="{{ $club->name }}" class="w-20 h-20 rounded-full object-cover mr-6">
                            @else
                                <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center mr-6">
                                    <span class="text-2xl font-bold text-gray-400">{{ substr($club->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-xl font-bold">{{ $club->name }}</h3>
                                <p class="text-gray-600">{{ $club->city }}, {{ $club->province }}</p>
                                <p class="text-gray-600">{{ $club->approved_players_count }} members</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-lg font-semibold text-gray-600">No Clubs Available</p>
                        <p class="text-sm text-gray-400 mt-2">There are no active clubs yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-dashboard-layout>
