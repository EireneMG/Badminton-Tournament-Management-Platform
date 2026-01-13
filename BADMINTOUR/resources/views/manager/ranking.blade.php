<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rankings Overview - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ 
    activeCategory: 'mens-singles',
    showPlayerModal: false,
    selectedPlayer: {
        name: 'John Doe',
        club: 'Manila Badminton Club',
        rank: 1,
        points: 2850,
        matchesPlayed: 24,
        wins: 20,
        losses: 4,
        winRate: 83.3
    },
    sortColumn: 'rank',
    sortDirection: 'asc',
    searchQuery: '',
    allRankings: @js($rankings->map(function($r) {
        return [
            'rank' => $r->rank,
            'player_name' => $r->player->first_name . ' ' . $r->player->last_name,
            'club_name' => $r->club ? $r->club->name : 'No Club',
            'matches_played' => $r->matches_played,
            'wins' => $r->wins,
            'losses' => $r->losses,
            'current_rating' => $r->current_rating,
            'is_provisional' => $r->is_provisional ?? false,
            'player_id' => $r->player->id,
            'player_name' => $r->player->first_name . ' ' . $r->player->last_name,
            'player' => [
                'first_name' => $r->player->first_name,
                'last_name' => $r->player->last_name,
            ],
            'club' => $r->club ? ['name' => $r->club->name] : null,
            'win_rate' => $r->win_rate,
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
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <div class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-[#2C5F4F]">RANKINGS OVERVIEW</h1>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('notifications.index') }}" class="relative">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <!-- Division Tabs (Primary Navigation) -->
                <div class="mb-4 flex flex-wrap gap-3">
                    @if(isset($divisions))
                        @foreach($divisions as $divKey => $divLabel)
                            <a href="{{ route('manager.ranking', ['category' => $category ?? 'All', 'division' => $divKey]) }}" 
                               class="px-6 py-3 rounded-lg font-bold text-sm transition duration-200 shadow-sm {{ ($division ?? 'All') === $divKey ? 'bg-[#2C5F4F] text-white shadow-md' : 'bg-white text-black border-2 border-[#2C5F4F] hover:bg-gray-50' }}">
                                {{ strtoupper($divLabel) }}
                            </a>
                        @endforeach
                    @endif
                </div>

                <!-- Category Dropdown (Secondary Navigation) -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tournament Category</label>
                    <select 
                        x-data="{ currentCategory: {{ json_encode($category ?? 'All') }}, currentDivision: {{ json_encode($division ?? 'All') }} }"
                        x-model="currentCategory"
                        @change="const url = new URL(window.location.href); url.searchParams.set('category', $event.target.value); if (currentDivision !== 'All') { url.searchParams.set('division', currentDivision); } window.location.href = url.toString();"
                        class="w-full max-w-md px-4 py-3 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] bg-white text-gray-900 font-semibold">
                        @if(isset($categories))
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ ($category ?? '') === $cat ? 'selected' : '' }}>
                                    {{ strtoupper($cat) }}
                                </option>
                            @endforeach
                        @else
                            <option value="All" {{ ($category ?? '') === 'All' ? 'selected' : '' }}>ALL</option>
                        @endif
                    </select>
                </div>

                <!-- Search + Filter Bar -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                        <div class="flex-1 w-full lg:w-auto relative">
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="Search player name or club" 
                                   class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="">Filter by Club</option>
                                @foreach($clubs as $filterClub)
                                    <option value="{{ $filterClub->id }}">{{ $filterClub->name }}</option>
                                @endforeach
                            </select>
                            <button class="bg-[#D4A574] hover:bg-[#C4956A] text-white px-6 py-2 rounded-lg font-semibold transition duration-200 shadow-sm">
                                Export Rankings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rankings Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#2C5F4F] text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('rank')">
                                        Rank <span x-text="getSortIcon('rank')" class="ml-1">↑</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('player_name')">
                                        Player Name <span x-text="getSortIcon('player_name')" class="ml-1">↕️</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('club_name')">
                                        Club <span x-text="getSortIcon('club_name')" class="ml-1">↕️</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('matches_played')">
                                        Matches Played <span x-text="getSortIcon('matches_played')" class="ml-1">↕️</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('wins')">
                                        Wins <span x-text="getSortIcon('wins')" class="ml-1">↕️</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('losses')">
                                        Losses <span x-text="getSortIcon('losses')" class="ml-1">↕️</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold cursor-pointer hover:bg-[#244D3E] transition" @click="sort('current_rating')">
                                        Points <span x-text="getSortIcon('current_rating')" class="ml-1">↕️</span>
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(ranking, index) in rankings" :key="index">
                                    <tr :class="index % 2 === 0 ? 'bg-white border-b border-gray-200 hover:bg-gray-50' : 'bg-gray-50 border-b border-gray-200 hover:bg-gray-100'" class="transition duration-150">
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
                                                name: ranking.player_name,
                                                club: ranking.club_name,
                                                rank: ranking.rank,
                                                rankDisplay: ranking.rank !== null ? ranking.rank : 'N/A',
                                                points: ranking.current_rating,
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
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                            No ranking data available yet.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Player Stats Modal -->
    <div x-show="showPlayerModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showPlayerModal = false"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-8" @click.stop>
                <!-- Close Button -->
                <button @click="showPlayerModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-[#2C5F4F]">Player Statistics</h2>
                </div>

                <!-- Player Details -->
                <div class="space-y-6">
                    <!-- Player Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Player Name</p>
                            <p class="text-xl font-bold text-gray-900" x-text="selectedPlayer.name"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Club</p>
                            <p class="text-xl font-bold text-gray-900" x-text="selectedPlayer.club"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Current Rank</p>
                            <p class="text-xl font-bold text-[#D4A574]" x-text="selectedPlayer.rank !== null ? '#' + selectedPlayer.rank : 'N/A (Provisional)'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500 mb-1">Total Points</p>
                            <p class="text-xl font-bold text-gray-900" x-text="selectedPlayer.points"></p>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500 mb-1">Matches Played</p>
                            <p class="text-2xl font-bold text-blue-600" x-text="selectedPlayer.matchesPlayed"></p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500 mb-1">Wins</p>
                            <p class="text-2xl font-bold text-green-600" x-text="selectedPlayer.wins"></p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500 mb-1">Losses</p>
                            <p class="text-2xl font-bold text-red-600" x-text="selectedPlayer.losses"></p>
                        </div>
                    </div>

                    <!-- Win Rate -->
                    <div class="bg-gradient-to-r from-[#2C5F4F] to-[#1B4965] rounded-lg p-6 text-center">
                        <p class="text-white text-sm mb-2">Win Rate</p>
                        <p class="text-4xl font-bold text-white" x-text="selectedPlayer.winRate + '%'"></p>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-end">
                    <button @click="showPlayerModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition duration-200">
                        Close
                    </button>
                    <a :href="'/players/' + selectedPlayer.playerId" class="bg-[#D4A574] hover:bg-[#C4956A] text-white px-6 py-3 rounded-lg font-semibold transition duration-200 text-center">
                        View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
