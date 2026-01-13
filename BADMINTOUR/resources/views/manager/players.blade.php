<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players List - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <div class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-[#2C5F4F]">PLAYERS LIST</h1>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8" x-data="{
                    searchQuery: '',
                    sortColumn: 'name',
                    sortDirection: 'asc',
                    showInviteModal: false,
                    selectedPlayer: null,
                    inviteMessage: '',
                    inviteError: '',
                    openInviteModal(player) {
                        this.selectedPlayer = player;
                        this.showInviteModal = true;
                        this.inviteMessage = '';
                        this.inviteError = '';
                    },
                    closeInviteModal() {
                        this.showInviteModal = false;
                        this.selectedPlayer = null;
                        this.inviteMessage = '';
                        this.inviteError = '';
                    },
                    submitInvite() {
                        this.inviteError = '';
                        document.getElementById('invite-form').submit();
                    },
                    allPlayers: @js($allPlayers->map(function($player) use ($club) {
                        $playerClubMembership = $player->approvedClubMembership;
                        $playerClub = $playerClubMembership?->club;
                        // Get ELO based on player's gender
                        $primaryCategory = ($player->gender === 'Female') ? 'WS' : 'MS';
                        $playerElo = \App\Models\EloRating::where('player_id', $player->id)->where('category', $primaryCategory)->first();
                        $displayElo = $playerElo ? $playerElo->current_rating : ($playerClubMembership?->provisional_elo ?? null);
                        // Get rank safely
                        $rankingPosition = null;
                        try {
                            $rankingService = app(\App\Services\RankingService::class);
                            $rankingPosition = $rankingService->getPlayerRanking($player, $primaryCategory);
                        } catch (\Exception $e) {
                            $rankingPosition = null;
                        }
                        $hasClubMembership = $player->clubMemberships()->where('status', 'approved')->exists();
                        $isInManagerClub = $club && $player->clubMemberships()->where('club_id', $club->id)->where('status', 'approved')->exists();
                        $hasPendingRequest = $club && $player->clubMemberships()->where('club_id', $club->id)->where('status', 'pending')->where('request_type', 'join_request')->exists();
                        $hasInvitation = $club && $player->clubMemberships()->where('club_id', $club->id)->where('status', 'invited')->where('request_type', 'invitation')->exists();
                        $biodataCompleted = $player->biodata_completed ?? false;
                        $canInvite = $club && $biodataCompleted && !$hasClubMembership && !$isInManagerClub && !$hasPendingRequest && !$hasInvitation;
                        return [
                            'id' => $player->id,
                            'name' => $player->first_name . ' ' . $player->last_name,
                            'profile_photo' => $player->profile_photo,
                            'club' => $playerClub?->name ?? 'No Club',
                            'elo' => $displayElo,
                            'rank' => $rankingPosition,
                            'status' => $player->getStatus(),
                            'can_invite' => $canInvite,
                            'has_invitation' => $hasInvitation,
                            'biodata_completed' => $biodataCompleted,
                        ];
                    })->toArray()),
                    get filteredPlayers() {
                        let filtered = this.allPlayers;
                        if (this.searchQuery) {
                            const query = this.searchQuery.toLowerCase();
                            filtered = filtered.filter(p => 
                                p.name.toLowerCase().includes(query) ||
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
                    },
                    getSortIcon(column) {
                        if (this.sortColumn !== column) return '↕️';
                        return this.sortDirection === 'asc' ? '↑' : '↓';
                    }
                }">
                <!-- Search Bar -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            placeholder="Search players by name or club" 
                            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Players List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
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
                                            <template x-if="!player.biodata_completed">
                                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">Not completed player profile</span>
                                            </template>
                                            <template x-if="player.biodata_completed && player.status === 'Active'">
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Active</span>
                                            </template>
                                            <template x-if="player.biodata_completed && player.status === 'Inactive'">
                                                <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">Inactive</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <a :href="`/players/${player.id}`" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                                    View Details
                                                </a>
                                                <template x-if="player.can_invite">
                                                    <button 
                                                        @click="openInviteModal(player)"
                                                        class="bg-[#D4A574] hover:bg-[#C49564] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                                        Invite
                                                    </button>
                                                </template>
                                                <template x-if="player.has_invitation">
                                                    <span class="text-xs text-amber-600 font-medium">Already invited</span>
                                                </template>
                                                <template x-if="!player.can_invite && !player.has_invitation && !player.biodata_completed">
                                                    <span class="text-xs text-gray-500">Cannot invite</span>
                                                </template>
                                            </div>
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

                <!-- Invite Player Modal -->
                <div 
                    x-show="showInviteModal" 
                    x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto" 
                    style="display: none;"
                    @keydown.escape.window="closeInviteModal()">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div 
                            @click="closeInviteModal()"
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#D4A574] bg-opacity-20 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-semibold text-gray-900">Invite Player to Club</h3>
                        <div class="mt-2">
                            <template x-if="selectedPlayer">
                                <p class="text-sm text-gray-500 mb-4">
                                    You are about to invite <strong x-text="selectedPlayer.name"></strong> to join your club. The player will receive an invitation and can accept or reject it.
                                </p>
                            </template>
                            <div class="mt-4">
                                <label for="invite_message" class="block text-sm font-medium text-gray-700 mb-2">Optional Message</label>
                                <textarea 
                                    id="invite_message" 
                                    x-model="inviteMessage"
                                    rows="3"
                                    placeholder="Add a personal message to the invitation (optional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]"></textarea>
                            </div>
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <p class="text-sm text-blue-800">
                                    <strong>Note:</strong> Skill level will be assigned after the player accepts the invitation, in the "Invited Players" section under "My Club".
                                </p>
                            </div>
                            <p x-show="inviteError" x-text="inviteError" class="mt-2 text-sm text-red-600"></p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <form action="{{ route('manager.club.invite') }}" method="POST" id="invite-form">
                        @csrf
                        <input type="hidden" name="player_id" x-bind:value="selectedPlayer ? selectedPlayer.id : ''">
                        <input type="hidden" name="message" x-bind:value="inviteMessage">
                        <button 
                            type="button" 
                            @click.prevent="submitInvite()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2C5F4F] text-base font-medium text-white hover:bg-[#244D3E] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:ml-3 sm:w-auto sm:text-sm">
                            Send Invitation
                        </button>
                    </form>
                    <button 
                        @click="closeInviteModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</body>
</html>
