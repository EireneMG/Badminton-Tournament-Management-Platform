<x-dashboard-layout greeting="WELCOME, MANAGER!" title="Manager Dashboard">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Tournaments -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Top Tournaments</h2>
                <a href="{{ route('manager.tournaments') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($topTournaments ?? [] as $t)
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-[#D4A574] transition">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $t->name }}</p>
                            <p class="text-xs text-gray-600">{{ $t->venue_name ?? 'Venue TBA' }}</p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ ucfirst($t->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No tournaments to show yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Top Clubs -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Top Clubs</h2>
                <a href="{{ route('manager.clubs') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($topClubs ?? [] as $club)
                    <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:border-[#D4A574] transition">
                        <div class="w-10 h-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($club->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $club->name }}</p>
                            <p class="text-xs text-gray-600">{{ $club->approved_players_count ?? 0 }} members</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No clubs to show yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Top Players -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Top Players</h2>
                <a href="{{ route('manager.players') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($topPlayers ?? [] as $index => $player)
                    <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg transition border border-gray-100">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-[#D4A574]' : 'bg-gray-400' }} text-white font-bold text-sm">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $player->first_name }} {{ $player->last_name }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ $player->approvedClubMembership->club->name ?? 'No Club' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No players to show yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Your Tournaments -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 lg:col-span-3">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Your Tournaments</h2>
                <a href="{{ route('manager.tournaments') }}" class="text-sm text-[#2C5F4F] hover:text-[#1B4965] font-semibold">Manage →</a>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($myTournaments ?? [] as $t)
                    <div class="p-3 border border-gray-200 rounded-lg hover:border-[#D4A574] transition bg-white">
                        <p class="font-semibold text-gray-900 text-sm">{{ $t->name }}</p>
                        <p class="text-xs text-gray-600">{{ ucfirst($t->status) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $t->start_date ? \Carbon\Carbon::parse($t->start_date)->format('M d, Y') : 'TBA' }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No tournaments yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-dashboard-layout>
