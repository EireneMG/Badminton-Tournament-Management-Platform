<x-dashboard-layout title="Players">
    <div class="max-w-7xl mx-auto">
        <!-- Search Bar -->
        <div class="mb-8">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Search players by name, email, or club"
                    class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                >
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Players List -->
        <div class="bg-white border-2 border-[#D4A574] rounded-lg overflow-hidden shadow-sm" id="playersList" x-data="{
            searchQuery: '',
            sortColumn: 'name',
            sortDirection: 'asc',
            allPlayers: @js($allPlayers->map(function($player) {
                $clubMembership = $player->approvedClubMembership;
                $club = $clubMembership?->club;
                // Get ELO based on player's gender
                $primaryCategory = ($player->gender === 'Female') ? 'WS' : 'MS';
                $playerElo = \App\Models\EloRating::where('player_id', $player->id)->where('category', $primaryCategory)->first();
                $displayElo = $playerElo ? $playerElo->current_rating : ($clubMembership?->provisional_elo ?? null);
                // Get rank safely
                $rankingPosition = null;
                try {
                    $rankingService = app(\App\Services\RankingService::class);
                    $rankingPosition = $rankingService->getPlayerRanking($player, $primaryCategory);
                } catch (\Exception $e) {
                    $rankingPosition = null;
                }
                return [
                    'id' => $player->id,
                    'name' => $player->first_name . ' ' . $player->last_name,
                    'email' => $player->email,
                    'profile_photo' => $player->profile_photo,
                    'club' => $club?->name ?? 'No Club',
                    'elo' => $displayElo,
                    'rank' => $rankingPosition,
                    'status' => $player->getStatus(),
                ];
            })->toArray()),
            get filteredPlayers() {
                let filtered = this.allPlayers;
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    filtered = filtered.filter(p => 
                        p.name.toLowerCase().includes(query) ||
                        p.email.toLowerCase().includes(query) ||
                        p.club.toLowerCase().includes(query)
                    );
                }
                return filtered;
            },
            get players() {
                let sorted = [...this.filteredPlayers];
                sorted.sort((a, b) => {
                    let aVal = a[this.sortColumn];
                    let bVal = b[this.sortColumn];
                    if (aVal === null || aVal === undefined) return 1;
                    if (bVal === null || bVal === undefined) return -1;
                    if (this.sortColumn === 'elo' || this.sortColumn === 'rank') {
                        aVal = aVal ?? 0;
                        bVal = bVal ?? 0;
                        return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                    }
                    aVal = String(aVal).toLowerCase();
                    bVal = String(bVal).toLowerCase();
                    if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
                return sorted;
            },
            sort(column) {
                if (this.sortColumn === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'asc';
                }
                this.players.sort((a, b) => {
                    let aVal = a[column];
                    let bVal = b[column];
                    if (aVal === null || aVal === undefined) return 1;
                    if (bVal === null || bVal === undefined) return -1;
                    if (column === 'elo' || column === 'rank') {
                        aVal = aVal ?? 0;
                        bVal = bVal ?? 0;
                        return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                    }
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-2">
                                    Player
                                    <span x-text="getSortIcon('name')"></span>
                                </div>
                            </th>
                            <th @click="sort('club')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-2">
                                    Club
                                    <span x-text="getSortIcon('club')"></span>
                                </div>
                            </th>
                            <th @click="sort('elo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-2">
                                    Elo Rating
                                    <span x-text="getSortIcon('elo')"></span>
                                </div>
                            </th>
                            <th @click="sort('rank')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-2">
                                    Rank
                                    <span x-text="getSortIcon('rank')"></span>
                                </div>
                            </th>
                            <th @click="sort('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-2">
                                    Status
                                    <span x-text="getSortIcon('status')"></span>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="player in players" :key="player.id">
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <template x-if="player.profile_photo">
                                            <img :src="`/storage/${player.profile_photo}`" :alt="player.name" class="h-10 w-10 rounded-full object-cover border-2 border-[#D4A574] mr-3">
                                        </template>
                                        <template x-if="!player.profile_photo">
                                            <div class="h-10 w-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-bold text-sm border-2 border-[#D4A574] mr-3" x-text="player.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)"></div>
                                        </template>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="player.name"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="player.club"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="player.elo">
                                        <div class="text-sm text-gray-900 font-semibold" x-text="new Intl.NumberFormat().format(player.elo)"></div>
                                    </template>
                                    <template x-if="!player.elo">
                                        <div class="text-sm text-gray-500">N/A</div>
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="player.rank">
                                        <div class="text-sm font-semibold text-[#2C5F4F]" x-text="`#${player.rank}`"></div>
                                    </template>
                                    <template x-if="!player.rank">
                                        <div class="text-sm text-gray-500">N/A</div>
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="player.status === 'Active'">
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Active</span>
                                    </template>
                                    <template x-if="player.status === 'Inactive'">
                                        <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">Inactive</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a :href="`/players/${player.id}`" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <template x-if="players.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No players found.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dashboard-layout>
