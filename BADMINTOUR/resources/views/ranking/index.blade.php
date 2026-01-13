<x-dashboard-layout title="Ranking">
    <div class="max-w-7xl mx-auto" x-data="{ 
        showPlayerModal: false, 
        selectedPlayer: {},
        sortColumn: 'rank',
        sortDirection: 'asc',
        searchQuery: '',
        allRankings: @js($rankings->map(function($r) {
            return [
                'rank' => $r['rank'],
                'player_id' => $r['player_id'] ?? null,
                'player_name' => strtoupper($r['player']->last_name) . ', ' . $r['player']->first_name,
                'club_name' => $r['club'] ? $r['club']->name : 'No Club',
                'matches_played' => $r['matches_played'],
                'wins' => $r['wins'],
                'losses' => $r['losses'],
                'current_rating' => $r['current_rating'],
                'is_provisional' => $r['is_provisional'] ?? false,
                'player' => [
                    'first_name' => $r['player']->first_name,
                    'last_name' => $r['player']->last_name,
                ],
                'club' => $r['club'] ? ['name' => $r['club']->name] : null,
                'win_rate' => $r['win_rate'],
                'peak_rating' => $r['peak_rating'],
            ];
        })->toArray()),
        
        get rankings() {
            if (!this.searchQuery.trim()) {
                return this.allRankings;
            }
            const query = this.searchQuery.toLowerCase().trim();
            return this.allRankings.filter(ranking => {
                const playerName = ranking.player_name.toLowerCase();
                const clubName = (ranking.club_name || '').toLowerCase();
                return playerName.includes(query) || clubName.includes(query);
            });
        },
        
        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            this.rankings.sort((a, b) => {
                let aVal = a[column];
                let bVal = b[column];
                
                // Handle null ranks (N/A) - always put them at bottom
                if (column === 'rank') {
                    if (aVal === null && bVal === null) return 0;
                    if (aVal === null) return 1; // a goes to bottom
                    if (bVal === null) return -1; // b goes to bottom
                }
                
                // Handle null club names
                if (column === 'club_name') {
                    aVal = aVal || '';
                    bVal = bVal || '';
                }
                
                // Numeric comparisons
                if (['rank', 'matches_played', 'wins', 'losses', 'current_rating'].includes(column)) {
                    aVal = aVal ?? 0;
                    bVal = bVal ?? 0;
                    return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                }
                
                // String comparisons
                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        getSortIcon(column) {
            if (this.sortColumn !== column) return '↕️';
            return this.sortDirection === 'asc' ? '↑' : '↓';
        }
    }">
        <!-- Division Tabs (Primary Navigation) -->
        <div class="bg-white border-2 border-[#2C5F4F] rounded-lg p-1 mb-4 inline-flex space-x-1 shadow-sm">
            @foreach($divisions as $divKey => $divLabel)
                <a href="{{ route('ranking.index', ['category' => $category, 'division' => $divKey]) }}" 
                   class="{{ $division === $divKey ? 'bg-[#2C5F4F] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50' }} px-6 py-3 rounded font-bold text-sm transition duration-200">
                    {{ strtoupper($divLabel) }}
                </a>
            @endforeach
        </div>

        <!-- Category Dropdown (Secondary Navigation) -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tournament Category</label>
            <select 
                x-data="{ currentCategory: {{ json_encode($category) }}, currentDivision: {{ json_encode($division) }} }"
                x-model="currentCategory"
                @change="const url = new URL(window.location.href); url.searchParams.set('category', $event.target.value); if (currentDivision !== 'All') { url.searchParams.set('division', currentDivision); } window.location.href = url.toString();"
                class="w-full max-w-md px-4 py-3 border-2 border-[#D4A574] rounded-lg focus:outline-none focus:border-[#2C5F4F] bg-white text-gray-900 font-semibold">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>
                        {{ strtoupper($cat) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Search player name or club"
                    class="w-full max-w-md px-4 py-3 pl-10 border-2 border-[#D4A574] rounded-lg focus:outline-none focus:border-[#2C5F4F] bg-white text-gray-900"
                >
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Ranking Table -->
        <div class="bg-white border-2 border-[#D4A574] rounded-lg overflow-hidden shadow-sm">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('rank')">
                            Rank <span x-text="getSortIcon('rank')" class="ml-1">↑</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('player_name')">
                            Player Name <span x-text="getSortIcon('player_name')" class="ml-1">↕️</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('club_name')">
                            Club <span x-text="getSortIcon('club_name')" class="ml-1">↕️</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('matches_played')">
                            Matches Played <span x-text="getSortIcon('matches_played')" class="ml-1">↕️</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('wins')">
                            Wins <span x-text="getSortIcon('wins')" class="ml-1">↕️</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('losses')">
                            Losses <span x-text="getSortIcon('losses')" class="ml-1">↕️</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase cursor-pointer hover:bg-gray-200 transition" @click="sort('current_rating')">
                            Points <span x-text="getSortIcon('current_rating')" class="ml-1">↕️</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <template x-for="(ranking, index) in rankings" :key="index">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <template x-if="ranking.rank !== null">
                                    <span :class="ranking.rank === 1 ? 'bg-[#D4A574]' : 'bg-gray-400'" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white font-bold" x-text="ranking.rank"></span>
                                </template>
                                <template x-if="ranking.rank === null">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-gray-600 font-bold text-xs">N/A</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                <span x-text="ranking.player_name"></span>
                                <template x-if="ranking.is_provisional || ranking.rank === null">
                                    <span class="text-xs text-gray-500 italic ml-2">(Provisional - No Rank)</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-gray-600" x-text="ranking.club_name"></td>
                            <td class="px-6 py-4 text-gray-600" x-text="ranking.matches_played"></td>
                            <td class="px-6 py-4 text-green-600 font-semibold" x-text="ranking.wins"></td>
                            <td class="px-6 py-4 text-red-600 font-semibold" x-text="ranking.losses"></td>
                            <td class="px-6 py-4 font-bold text-gray-900" x-text="new Intl.NumberFormat().format(ranking.current_rating)"></td>
                            <td class="px-6 py-4">
                                <button @click="showPlayerModal = true; selectedPlayer = {
                                    playerId: ranking.player_id,
                                    name: ranking.player.first_name + ' ' + ranking.player.last_name,
                                    club: ranking.club_name,
                                    rank: ranking.rank,
                                    rankDisplay: ranking.rank !== null ? ranking.rank : 'N/A',
                                    rating: ranking.current_rating,
                                    peakRating: ranking.peak_rating,
                                    matchesPlayed: ranking.matches_played,
                                    wins: ranking.wins,
                                    losses: ranking.losses,
                                    winRate: ranking.win_rate
                                }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                    View Stats
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="rankings.length === 0">
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <p class="text-lg font-semibold">No Rankings Available</p>
                                    <p class="text-sm text-gray-400 mt-2">No players have ELO ratings in this category yet.</p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Player Stats Modal -->
        <div x-show="showPlayerModal" 
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showPlayerModal = false">
            <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4" @click.stop>
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold" x-text="selectedPlayer.name"></h2>
                        <p class="text-gray-600" x-text="selectedPlayer.club"></p>
                    </div>
                    <button @click="showPlayerModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="border-2 border-[#D4A574] rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">RANK</p>
                        <p class="text-2xl font-bold" x-text="selectedPlayer.rank !== null ? '#' + selectedPlayer.rank : 'N/A (Provisional)'"></p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">CURRENT RATING</p>
                        <p class="text-2xl font-bold text-[#C85A54]" x-text="selectedPlayer.rating"></p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">PEAK RATING</p>
                        <p class="text-2xl font-bold text-[#D4A574]" x-text="selectedPlayer.peakRating"></p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">MATCHES PLAYED</p>
                        <p class="text-2xl font-bold" x-text="selectedPlayer.matchesPlayed"></p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">WINS</p>
                        <p class="text-2xl font-bold text-green-600" x-text="selectedPlayer.wins"></p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">LOSSES</p>
                        <p class="text-2xl font-bold text-red-600" x-text="selectedPlayer.losses"></p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-semibold">Win Rate</span>
                        <span class="text-2xl font-bold text-[#2C5F4F]" x-text="selectedPlayer.winRate + '%'"></span>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="mt-6 flex flex-col sm:flex-row gap-4 justify-end">
                    <button @click="showPlayerModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition duration-200">
                        Close
                    </button>
                    <template x-if="selectedPlayer.playerId">
                        <a :href="'{{ url('/players') }}/' + selectedPlayer.playerId" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200 text-center">
                            View Profile
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
