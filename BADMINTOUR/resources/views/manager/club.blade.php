<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white" x-data="{ 
    showInviteModal: false,
    showBiodataModal: false,
    showRemoveConfirm: false,
    removePlayerId: null,
    removePlayerName: '',
    biodataPlayer: null,
    biodataLoading: false,
    biodataError: null,
    showAssignProvisionalModal: false,
    selectedInvitedPlayerId: null,
    showStatisticsModal: false,
    openBiodataModal(playerId) {
        this.showBiodataModal = true;
        this.biodataLoading = true;
        this.biodataError = null;
        this.biodataPlayer = null;

        fetch(`/manager/players/${playerId}/biodata`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load biodata');
            }
            return response.json();
        })
        .then(data => {
            this.biodataPlayer = data.player;
            this.biodataLoading = false;
        })
        .catch(error => {
            this.biodataError = 'Failed to load player biodata. Please try again.';
            this.biodataLoading = false;
        });
    }
}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-[#2C5F4F]">Club Management</h1>
                        <p class="text-sm text-gray-500">Manage your club, players, and invitations</p>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @if(!$club)
                    <!-- No Club Created Yet -->
                    <div class="bg-white rounded-lg shadow-sm p-12 border border-gray-200 text-center">
                        <div class="max-w-md mx-auto">
                            <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <h2 class="text-2xl font-semibold text-[#2C5F4F] mb-2">No Club Created Yet</h2>
                            <p class="text-gray-600 mb-6">You haven't created a club yet. Create one to start managing tournaments and players.</p>
                            <a href="{{ route('manager.create-club') }}" class="inline-flex items-center px-6 py-3 bg-[#2C5F4F] hover:bg-[#244D3E] text-white font-semibold rounded-lg transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Your Club
                            </a>
                        </div>
                    </div>
                @else
                <!-- Club Overview Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 mb-6">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1">
                            <div class="flex items-start gap-4 mb-4">
                                @if($club->logo)
                                    <div class="flex-shrink-0">
                                        <img src="{{ Storage::url($club->logo) }}" alt="{{ $club->name }} Logo" class="w-20 h-20 rounded-lg object-cover border-2 border-[#D4A574] shadow-sm">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-20 h-20 rounded-lg bg-[#2C5F4F] flex items-center justify-center border-2 border-[#D4A574] shadow-sm">
                                        <span class="text-white text-2xl font-bold">{{ strtoupper(substr($club->name, 0, 2)) }}</span>
                                    </div>
                                @endif
                                
                                <div class="flex-1">
                                    <h2 class="text-2xl font-semibold text-[#2C5F4F] mb-2">{{ $club->name }}</h2>
                                    <div class="space-y-2 text-gray-600">
                                        <p class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>{{ $club->city }}, {{ $club->province }}</span>
                                        </p>
                                        <p class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span><strong>{{ $approvedPlayers->count() }}</strong> Members</span>
                                        </p>
                                        <p class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>Created: {{ $club->created_at->format('F d, Y') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($club->description)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-700 mb-2">About Our Club</h3>
                                    <p class="text-sm text-gray-600 leading-relaxed">{{ $club->description }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-3 ml-4">
                            <a href="{{ route('manager.club.edit') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md shadow-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Club Info
                            </a>
                            <button @click="showInviteModal = true" class="bg-[#D4A574] hover:bg-[#C49564] text-white px-4 py-2 rounded-md shadow-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Invite Player
                            </button>
                            <button @click="showStatisticsModal = true" class="bg-[#C85A54] hover:bg-[#B04A44] text-white px-4 py-2 rounded-md shadow-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                View Club Statistics
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Pending Join Requests and Invited Players Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Pending Join Requests Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200"
                         x-data="{ showApproveModalId: null, showRejectModalId: null }">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-[#2C5F4F]">Pending Join Requests <span class="text-sm font-normal text-gray-500">({{ $joinRequests->count() }})</span></h2>
                        </div>
                    
                    @if($joinRequests->count() > 0)
                        <div class="overflow-x-auto max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biodata</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($joinRequests as $joinRequest)
                        @php
                            $player = $joinRequest->player;
                            $age = null;
                            if ($player->birth_year && $player->birth_month && $player->birth_day) {
                                $birthDate = \Carbon\Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
                                $age = (int)$birthDate->diffInYears(\Carbon\Carbon::now());
                            }
                            $historyLabels = [
                                'tournament' => 'Tournament experience',
                                'school_event' => 'School event',
                                'community_event' => 'Community event',
                                'club_member' => 'Club member',
                                'recreational' => 'Recreational',
                                'coached' => 'Coached',
                                'varsity' => 'Varsity player',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($player->profile_photo)
                                        <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->first_name }}" class="h-10 w-10 rounded-full object-cover border border-gray-300 mr-3">
                                    @else
                                        <div class="h-10 w-10 bg-[#2C5F4F] rounded-full flex items-center justify-center text-white font-semibold text-sm mr-3">
                                            {{ strtoupper(substr($player->first_name, 0, 1) . substr($player->last_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="text-sm font-medium text-gray-900">{{ $player->first_name }} {{ $player->last_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $player->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($player->biodata_completed)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Complete</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Incomplete</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $joinRequest->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="openBiodataModal({{ $player->id }})" 
                                        class="bg-[#D4A574] hover:bg-[#C49564] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                        View Biodata
                                    </button>
                                    <button 
                                        @click="showApproveModalId = {{ $joinRequest->id }}" 
                                        class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                        Approve
                                    </button>
                                    <button 
                                        @click="showRejectModalId = {{ $joinRequest->id }}"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Approve Confirmation Modal -->
                        <div 
                            x-show="showApproveModalId === {{ $joinRequest->id }}" 
                            x-cloak
                            class="fixed inset-0 z-50 overflow-y-auto" 
                            style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div 
                                    @click="showApproveModalId = null"
                                    class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>
                                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                            <h3 class="text-lg leading-6 font-semibold text-gray-900">Assign Provisional Skill Level</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500 mb-3">
                                                    Please assign a provisional skill level for this player. This will be converted to their official ELO rating.
                                                </p>
                                                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                                    <p class="text-sm text-yellow-800">
                                                        <strong>Important:</strong> This provisional skill level will be converted to the player's official ranking/ELO rating and cannot be modified by the manager afterward. Future changes will come automatically from match results.
                                                    </p>
                                                </div>
                                                <div class="mt-4">
                                                    <label for="provisional_skill_level_{{ $joinRequest->id }}" class="block text-sm font-medium text-gray-700 mb-2">Provisional Skill Level</label>
                                                    <select 
                                                        id="provisional_skill_level_{{ $joinRequest->id }}" 
                                                        name="provisional_skill_level" 
                                                        required
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                                        <option value="">Select Skill Level</option>
                                                        <option value="A">Level A (Advanced) - ELO: 1800</option>
                                                        <option value="B">Level B (Intermediate-Advanced) - ELO: 1600</option>
                                                        <option value="C">Level C (Intermediate) - ELO: 1400</option>
                                                        <option value="D">Level D (Beginner) - ELO: 1200</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <form action="{{ route('club-players.approve', $joinRequest) }}" method="POST" class="inline" id="approve-form-{{ $joinRequest->id }}">
                                            @csrf
                                            <input type="hidden" name="provisional_skill_level" id="hidden_skill_level_{{ $joinRequest->id }}" value="">
                                            <button 
                                                type="submit" 
                                                @click.prevent="
                                                    const skillLevel = document.getElementById('provisional_skill_level_{{ $joinRequest->id }}').value;
                                                    if (!skillLevel) {
                                                        document.getElementById('skill_level_error_{{ $joinRequest->id }}').classList.remove('hidden');
                                                        return false;
                                                    }
                                                    document.getElementById('hidden_skill_level_{{ $joinRequest->id }}').value = skillLevel;
                                                    document.getElementById('approve-form-{{ $joinRequest->id }}').submit();
                                                "
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2C5F4F] text-base font-medium text-white hover:bg-[#244D3E] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:ml-3 sm:w-auto sm:text-sm">
                                                Assign & Approve Player
                                            </button>
                                            <p id="skill_level_error_{{ $joinRequest->id }}" class="hidden mt-2 text-sm text-red-600">Please select a provisional skill level before approving.</p>
                                        </form>
                                        <button 
                                            @click="showApproveModalId = null"
                                            type="button" 
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:mt-0 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <!-- Reject Confirmation Modal -->
                        <div 
                            x-show="showRejectModalId === {{ $joinRequest->id }}"
                            x-cloak
                            class="fixed inset-0 z-50 overflow-y-auto"
                            style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div 
                                    @click="showRejectModalId = null"
                                    class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>
                                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 5a7 7 0 100 14 7 7 0 000-14z"></path>
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                            <h3 class="text-lg leading-6 font-semibold text-gray-900">Reject Join Request</h3>
                                            <p class="mt-2 text-sm text-gray-500">
                                                Are you sure you want to reject this player's request to join your club?
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <form action="{{ route('club-players.reject', $joinRequest) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Yes, Reject
                                            </button>
                                        </form>
                                        <button 
                                            @click="showRejectModalId = null"
                                            type="button"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 sm:mt-0 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-8 text-center text-gray-500">
                            <p>No pending join requests.</p>
                        </div>
                    @endif
                    </div>

                    <!-- Invited Players Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-[#2C5F4F]">Invited Players <span class="text-sm font-normal text-gray-500">({{ $invitedPlayers->count() }})</span></h2>
                        </div>
                        
                        @if($invitedPlayers->count() > 0)
                            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invited</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($invitedPlayers as $invitedPlayer)
                                        @php
                                            $player = $invitedPlayer->player;
                                            $status = $invitedPlayer->status;
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($player->profile_photo)
                                                        <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->first_name }}" class="h-10 w-10 rounded-full object-cover border border-gray-300 mr-3">
                                                    @else
                                                        <div class="h-10 w-10 bg-[#2C5F4F] rounded-full flex items-center justify-center text-white font-semibold text-sm mr-3">
                                                            {{ strtoupper(substr($player->first_name, 0, 1) . substr($player->last_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $player->first_name }} {{ $player->last_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $player->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($status === 'invited')
                                                    @if(!$invitedPlayer->skill_level || !$invitedPlayer->provisional_elo)
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">Accepted - Awaiting Skill Level</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
                                                    @endif
                                                @elseif($status === 'approved')
                                                    @if(!$invitedPlayer->skill_level || !$invitedPlayer->provisional_elo)
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">Accepted - Awaiting Skill Level</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Accepted</span>
                                                    @endif
                                                @elseif($status === 'rejected')
                                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">Rejected</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full">{{ ucfirst($status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $invitedPlayer->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($status === 'invited' || ($status === 'approved' && (!$invitedPlayer->skill_level || !$invitedPlayer->provisional_elo)))
                                                    @if(!$invitedPlayer->skill_level || !$invitedPlayer->provisional_elo)
                                                        <!-- Player has accepted invitation, waiting for provisional skill level -->
                                                        <button 
                                                            @click="openBiodataModal({{ $player->id }})" 
                                                            class="bg-[#D4A574] hover:bg-[#C49564] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200 mr-2">
                                                            View Biodata
                                                        </button>
                                                        <button 
                                                            @click="selectedInvitedPlayerId = {{ $invitedPlayer->id }}; showAssignProvisionalModal = true" 
                                                            class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                                            Assign Provisional Level
                                                        </button>
                                                    @else
                                                        <!-- Invitation still pending acceptance -->
                                                        <form action="{{ route('manager.club.remove-player', $invitedPlayer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this invitation?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                                                Cancel Invitation
                                                            </button>
                                                        </form>
                                                    @endif
                                                @elseif($status === 'approved' && $invitedPlayer->skill_level && $invitedPlayer->provisional_elo)
                                                    <!-- Player is fully approved with provisional skill level -->
                                                    <button 
                                                        @click="openBiodataModal({{ $player->id }})" 
                                                        class="bg-[#D4A574] hover:bg-[#C49564] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                                        View Biodata
                                                    </button>
                                                @elseif($status === 'rejected')
                                                    <form action="{{ route('manager.club.remove-player', $invitedPlayer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this rejected invitation?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                                            Remove
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-6 py-8 text-center text-gray-500">
                                <p>No invited players.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Club Members Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200" x-data="{
                    sortColumn: 'name',
                    sortDirection: 'asc',
                    members: @js($approvedPlayers->map(function($clubPlayer) {
                        $category = ($clubPlayer->player && $clubPlayer->player->gender === 'Female') ? 'WS' : 'MS';
                        $playerElo = \App\Models\EloRating::where('player_id', $clubPlayer->player_id)->where('category', $category)->first();
                        $displayElo = $playerElo ? $playerElo->current_rating : ($clubPlayer->provisional_elo ?? null);
                        // Official ranking: has ELO rating AND has played matches
                        $hasOfficialRanking = $playerElo && $playerElo->matches_played > 0;
                        // Provisional: has ELO but no matches played, OR has provisional_elo but no ELO rating yet
                        $isProvisional = ($playerElo && $playerElo->matches_played === 0) || (!$playerElo && $clubPlayer->provisional_elo);
                        $playerRanking = null;
                        if ($hasOfficialRanking) {
                            $rankingService = app(\App\Services\RankingService::class);
                            $playerRanking = $rankingService->getPlayerRanking($clubPlayer->player, $category);
                        }
                        return [
                            'id' => $clubPlayer->id,
                            'player_id' => $clubPlayer->player_id,
                            'name' => ($clubPlayer->player ? ($clubPlayer->player->first_name . ' ' . $clubPlayer->player->last_name) : 'N/A'),
                            'email' => $clubPlayer->player?->email ?? 'N/A',
                            'profile_photo' => $clubPlayer->player?->profile_photo ?? null,
                            'skill_level' => $clubPlayer->skill_level,
                            'is_provisional' => $isProvisional,
                            'elo_rating' => $displayElo,
                            'rank' => $playerRanking,
                            'has_ranking' => $hasOfficialRanking,
                            'category' => $category,
                        ];
                    })->toArray()),
                    sort(column) {
                        if (this.sortColumn === column) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortColumn = column;
                            this.sortDirection = 'asc';
                        }
                        this.members.sort((a, b) => {
                            let aVal = a[column];
                            let bVal = b[column];
                            if (aVal === null || aVal === undefined) return 1;
                            if (bVal === null || bVal === undefined) return -1;
                            if (column === 'elo_rating' || column === 'rank') {
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
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-[#2C5F4F]">Club Members <span class="text-sm font-normal text-gray-500">({{ $approvedPlayers->count() }})</span></h2>
                    </div>
                    
                    <div class="overflow-x-auto px-6 py-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            Player Name
                                            <span x-text="getSortIcon('name')"></span>
                                        </div>
                                    </th>
                                    <th @click="sort('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            Email
                                            <span x-text="getSortIcon('email')"></span>
                                        </div>
                                    </th>
                                    <th @click="sort('skill_level')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            Skill Level
                                            <span x-text="getSortIcon('skill_level')"></span>
                                        </div>
                                    </th>
                                    <th @click="sort('elo_rating')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            ELO Rating
                                            <span x-text="getSortIcon('elo_rating')"></span>
                                        </div>
                                    </th>
                                    <th @click="sort('rank')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            Rank
                                            <span x-text="getSortIcon('rank')"></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="clubPlayer in members" :key="clubPlayer.id">
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <template x-if="clubPlayer.profile_photo">
                                                    <img :src="`/storage/${clubPlayer.profile_photo}`" :alt="clubPlayer.name" class="h-10 w-10 rounded-full object-cover border-2 border-[#D4A574] mr-3">
                                                </template>
                                                <template x-if="!clubPlayer.profile_photo">
                                                    <div class="h-10 w-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-semibold border-2 border-[#D4A574] mr-3" x-text="clubPlayer.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)"></div>
                                                </template>
                                                <div class="text-sm font-medium text-gray-900" x-text="clubPlayer.name"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500" x-text="clubPlayer.email"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <template x-if="clubPlayer.skill_level">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-3 py-1 text-xs font-medium rounded-md" :class="{
                                                        'bg-purple-100 text-purple-800': clubPlayer.skill_level === 'A',
                                                        'bg-blue-100 text-blue-800': clubPlayer.skill_level === 'B',
                                                        'bg-green-100 text-green-800': clubPlayer.skill_level === 'C',
                                                        'bg-gray-100 text-gray-800': !['A', 'B', 'C'].includes(clubPlayer.skill_level)
                                                    }" x-text="`Level ${clubPlayer.skill_level}`"></span>
                                                    <template x-if="clubPlayer.is_provisional">
                                                        <span class="text-xs text-gray-500 italic">(Provisional)</span>
                                                    </template>
                                                    <template x-if="!clubPlayer.is_provisional && clubPlayer.has_ranking">
                                                        <span class="text-xs text-[#2C5F4F] font-semibold">(Official)</span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!clubPlayer.skill_level">
                                                <div class="text-sm text-gray-500 italic">No ranking yet</div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <template x-if="clubPlayer.elo_rating">
                                                <div class="text-sm text-gray-900 font-semibold" x-text="new Intl.NumberFormat().format(clubPlayer.elo_rating)"></div>
                                            </template>
                                            <template x-if="!clubPlayer.elo_rating">
                                                <div class="text-sm text-gray-500">N/A</div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <template x-if="clubPlayer.has_ranking && clubPlayer.rank">
                                                <div class="text-sm font-semibold text-[#2C5F4F]" x-text="`#${clubPlayer.rank}`"></div>
                                            </template>
                                            <template x-if="clubPlayer.is_provisional">
                                                <div class="text-sm text-gray-500 italic">Provisional - No Ranking</div>
                                            </template>
                                            <template x-if="!clubPlayer.has_ranking && !clubPlayer.is_provisional">
                                                <div class="text-sm text-gray-500">N/A</div>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <button @click="openBiodataModal(clubPlayer.player_id)" class="bg-[#D4A574] hover:bg-[#C49564] text-white px-3 py-1.5 rounded text-xs font-medium transition duration-200">
                                                View Biodata
                                            </button>
                                            <span class="text-gray-300 mx-2">|</span>
                                            <button @click="removePlayerId = clubPlayer.id; removePlayerName = clubPlayer.name; showRemoveConfirm = true;" class="text-red-600 hover:text-red-800 font-medium">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="members.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No club members yet.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Invite Player Modal -->
    <div x-show="showInviteModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showInviteModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showInviteModal = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showInviteModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <form action="{{ route('manager.club.invite') }}" method="POST">
                    @csrf
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#2C5F4F] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-[#2C5F4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-semibold text-[#2C5F4F]" id="modal-title">
                                Invite Player to Club
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="player_email" class="block text-sm font-medium text-gray-700 mb-1">Player Email <span class="text-red-500">*</span></label>
                                    <input 
                                        type="email" 
                                        id="player_email" 
                                        name="player_email"
                                        placeholder="player@email.com"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]"
                                    >
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message (Optional)</label>
                                    <textarea 
                                        id="message" 
                                        name="message"
                                        rows="4"
                                        placeholder="Write a personal message to the player..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F] resize-none"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                            Send Invitation
                        </button>
                        <button type="button" 
                                @click="showInviteModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 sm:mt-0 sm:w-auto sm:text-sm transition duration-200">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Provisional Skill Level Modals -->
    @foreach($invitedPlayers as $invitedPlayer)
        @if(($invitedPlayer->status === 'invited' || $invitedPlayer->status === 'approved') && (!$invitedPlayer->skill_level || !$invitedPlayer->provisional_elo))
            <div x-show="showAssignProvisionalModal && selectedInvitedPlayerId === {{ $invitedPlayer->id }}" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 aria-labelledby="modal-title" 
                 role="dialog" 
                 aria-modal="true"
                 style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div x-show="showAssignProvisionalModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="showAssignProvisionalModal = false; selectedInvitedPlayerId = null"
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity" 
                         aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal panel -->
                    <div x-show="showAssignProvisionalModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        
                        <form action="{{ route('manager.club.assign-provisional', $invitedPlayer) }}" method="POST">
                            @csrf
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#2C5F4F] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-[#2C5F4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg leading-6 font-semibold text-[#2C5F4F]" id="modal-title">
                                        Assign Provisional Skill Level
                                    </h3>
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Assign a provisional skill level for <strong>{{ $invitedPlayer->player ? ($invitedPlayer->player->first_name . ' ' . $invitedPlayer->player->last_name) : 'N/A' }}</strong>. This will be converted to their official ELO rating.
                                        </p>
                                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-yellow-700">
                                                        <strong>Important:</strong> Once you confirm the provisional level, it cannot be changed. Future changes will come automatically from match results.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="provisional_skill_level_{{ $invitedPlayer->id }}" class="block text-sm font-medium text-gray-700 mb-2">Provisional Skill Level <span class="text-red-500">*</span></label>
                                            <select 
                                                id="provisional_skill_level_{{ $invitedPlayer->id }}" 
                                                name="provisional_skill_level"
                                                required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]"
                                            >
                                                <option value="">Select Skill Level</option>
                                                <option value="A">Level A (Advanced) - ELO: 1800</option>
                                                <option value="B">Level B (Intermediate-Advanced) - ELO: 1600</option>
                                                <option value="C">Level C (Intermediate) - ELO: 1400</option>
                                                <option value="D">Level D (Beginner) - ELO: 1200</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="submit" 
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                                    Assign & Confirm
                                </button>
                                <button type="button" 
                                        @click="showAssignProvisionalModal = false; selectedInvitedPlayerId = null"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 sm:mt-0 sm:w-auto sm:text-sm transition duration-200">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Biodata Modal -->
    <div 
        x-show="showBiodataModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;"
        @click.away="showBiodataModal = false; biodataPlayer = null; biodataError = null;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div 
                @click="showBiodataModal = false; biodataPlayer = null; biodataError = null;"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <!-- Modal Header -->
                <div class="bg-[#2C5F4F] px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Profile Photo in Header -->
                            <div x-show="biodataPlayer && biodataPlayer.profile_photo" class="flex-shrink-0">
                                <img :src="biodataPlayer ? biodataPlayer.profile_photo : ''" alt="Profile Photo" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-lg">
                            </div>
                            <div x-show="biodataPlayer && !biodataPlayer.profile_photo" class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-[#2C5F4F] text-2xl font-bold border-2 border-white shadow-lg">
                                    <span x-text="(biodataPlayer && biodataPlayer.first_name ? biodataPlayer.first_name[0] : 'U').toUpperCase()"></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">Player Biodata</h3>
                                <p class="text-sm text-gray-200" x-show="biodataPlayer" x-text="biodataPlayer ? (biodataPlayer.first_name + ' ' + biodataPlayer.last_name) : ''"></p>
                            </div>
                        </div>
                        <button 
                            @click="showBiodataModal = false; biodataPlayer = null; biodataError = null;"
                            class="text-white hover:text-gray-200 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                    <!-- Loading State -->
                    <div x-show="biodataLoading" class="text-center py-12">
                        <svg class="animate-spin h-12 w-12 text-[#2C5F4F] mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-4 text-gray-600">Loading biodata...</p>
                    </div>

                    <!-- Error State -->
                    <div x-show="biodataError && !biodataLoading" class="text-center py-12">
                        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-600 font-semibold" x-text="biodataError"></p>
                    </div>

                    <!-- Biodata Content -->
                    <div x-if="biodataPlayer && !biodataLoading && !biodataError" class="space-y-6">
                        <!-- Personal Information -->
                        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Personal Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Full Name</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? (biodataPlayer.first_name + ' ' + (biodataPlayer.middle_name || '') + ' ' + biodataPlayer.last_name) : ''"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Email</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.email : ''"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Contact Number</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? (biodataPlayer.contact_number || 'N/A') : 'N/A'"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Gender</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.gender : ''"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Date of Birth</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.birth_date : ''"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Age</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.age : ''"></p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600 mb-1">Location</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? (biodataPlayer.location || 'N/A') : 'N/A'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Attributes -->
                        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Physical Attributes</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Height</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.height : ''"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Weight</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.weight : ''"></p>
                                </div>
                            </div>
                        </div>

                        <!-- School Information -->
                        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]" x-show="biodataPlayer && (biodataPlayer.school_status !== 'N/A' || biodataPlayer.school_name !== 'N/A')">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">School Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">School Status</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.school_status : ''"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">School Name</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? biodataPlayer.school_name : ''"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Badminton Experience -->
                        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Badminton Experience</h3>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Years of Experience</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? (biodataPlayer.years_of_experience || 'N/A') : 'N/A'"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Experience Level</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? (biodataPlayer.experience_level || 'N/A') : 'N/A'"></p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600 mb-1">Competitive Background</p>
                                    <p class="font-semibold" x-text="biodataPlayer ? (biodataPlayer.competitive_background || 'N/A') : 'N/A'"></p>
                                </div>
                            </div>
                            <div x-show="biodataPlayer && biodataPlayer.badminton_history && biodataPlayer.badminton_history.length > 0">
                                <p class="text-sm text-gray-600 mb-2">Additional Background:</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="history in (biodataPlayer ? biodataPlayer.badminton_history : [])" :key="history">
                                        <span class="px-3 py-1 bg-[#2C5F4F] text-white rounded-full text-sm font-medium" x-text="history"></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Photo (Large View) -->
                        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Profile Photo</h3>
                            <div class="flex justify-center">
                                <div x-show="biodataPlayer && biodataPlayer.profile_photo">
                                    <img :src="biodataPlayer ? biodataPlayer.profile_photo : ''" alt="Profile Photo" class="w-48 h-48 rounded-lg object-cover border-2 border-[#D4A574] shadow-lg">
                                </div>
                                <div x-show="!biodataPlayer || !biodataPlayer.profile_photo" class="w-48 h-48 rounded-lg bg-gray-100 border-2 border-[#D4A574] flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500">No profile photo</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ID Document -->
                        <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]" x-show="biodataPlayer && biodataPlayer.player_id_document">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">ID Document</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">ID Type</p>
                                    <p class="font-semibold" x-text="(biodataPlayer && biodataPlayer.id_type) ? biodataPlayer.id_type : 'N/A'"></p>
                                </div>
                                <div class="flex justify-center">
                                    <a :href="biodataPlayer ? biodataPlayer.player_id_document : '#'" target="_blank" class="inline-flex items-center px-4 py-2 bg-[#2C5F4F] text-white rounded-lg hover:bg-[#244D3E] transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View ID Document
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button 
                        @click="showBiodataModal = false; biodataPlayer = null; biodataError = null;"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Player Confirmation Modal -->
    <div x-show="showRemoveConfirm" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm Removal</h3>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to remove <strong x-text="removePlayerName"></strong> from your club? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        @click="showRemoveConfirm = false; removePlayerId = null; removePlayerName = '';"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition duration-200">
                        Cancel
                    </button>
                    <form :action="`{{ url('/manager/club/players') }}/${removePlayerId}`" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition duration-200">
                            Remove Player
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($clubStatistics)
    <div x-show="showStatisticsModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.self="showStatisticsModal = false" @keydown.escape.window="showStatisticsModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                <h2 class="text-2xl font-bold text-[#2C5F4F]">Club Statistics</h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('manager.club.statistics.export') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md text-sm font-medium transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Statistics
                    </a>
                    <button @click="showStatisticsModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">TOTAL MEMBERS</p>
                        <p class="text-2xl font-bold text-[#2C5F4F]">{{ $clubStatistics['total_members'] ?? 0 }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">TOURNAMENTS HOSTED</p>
                        <p class="text-2xl font-bold text-[#2C5F4F]">{{ $clubStatistics['tournaments_hosted'] ?? 0 }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">TOTAL MATCHES</p>
                        <p class="text-2xl font-bold text-[#2C5F4F]">{{ $clubStatistics['total_matches'] ?? 0 }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">AVG MEMBER ELO</p>
                        <p class="text-2xl font-bold text-[#2C5F4F]">{{ number_format($clubStatistics['average_elo'] ?? 0) }}</p>
                    </div>
                </div>
                @if(isset($clubStatistics['tournaments_by_status']))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-4">Tournaments by Status</p>
                    <div class="grid grid-cols-4 gap-3">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Published</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $clubStatistics['tournaments_by_status']['published'] ?? 0 }}</p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Upcoming</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $clubStatistics['tournaments_by_status']['upcoming'] ?? 0 }}</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Ongoing</p>
                            <p class="text-2xl font-bold text-green-600">{{ $clubStatistics['tournaments_by_status']['ongoing'] ?? 0 }}</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Completed</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $clubStatistics['tournaments_by_status']['completed'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</body>
</html>
