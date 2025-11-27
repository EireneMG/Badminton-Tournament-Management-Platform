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
<body class="bg-white" x-data="{ showInviteModal: false }">
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
                <!-- Club Overview & Player List Grid -->
                <div class="grid lg:grid-cols-2 gap-6 mb-8">
                    <!-- Club Overview Card -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex justify-between items-start mb-6">
                        <div>
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
                        <div class="flex gap-3">
                            <button class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md shadow-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Club Info
                            </button>
                            <button @click="showInviteModal = true" class="bg-[#D4A574] hover:bg-[#C49564] text-white px-4 py-2 rounded-md shadow-sm transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Invite Player
                            </button>
                        </div>
                    </div>
                    </div>

                    <!-- Player List Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold text-[#2C5F4F] mb-4">Club Players</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skill Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($approvedPlayers as $clubPlayer)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-[#2C5F4F] rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($clubPlayer->player->first_name, 0, 1) . substr($clubPlayer->player->last_name, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $clubPlayer->player->first_name }} {{ $clubPlayer->player->last_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $clubPlayer->player->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:border-[#2C5F4F]">
                                            <option value="">Assign Level</option>
                                            <option value="A">Level A</option>
                                            <option value="B">Level B</option>
                                            <option value="C">Level C</option>
                                            <option value="D">Level D</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                        <button class="text-[#2C5F4F] hover:text-[#244D3E] font-medium">View Profile</button>
                                        <span class="text-gray-300">|</span>
                                        <button class="text-red-600 hover:text-red-800 font-medium">Remove</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No approved players yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>

                <!-- Pending Join Requests Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold text-[#2C5F4F] mb-4">Pending Join Requests</h2>
                    
                    <div class="space-y-4">
                        @forelse($joinRequests as $joinRequest)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-semibold">
                                        {{ strtoupper(substr($joinRequest->player->first_name, 0, 1) . substr($joinRequest->player->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">{{ $joinRequest->player->first_name }} {{ $joinRequest->player->last_name }}</h3>
                                        <p class="text-sm text-gray-500">Requested to join: {{ $club->name }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $joinRequest->created_at->format('F d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <form action="{{ route('manager.club.approve', $joinRequest->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('manager.club.reject', $joinRequest->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-8">No pending join requests.</p>
                        @endforelse
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
                            <!-- Player Email -->
                            <div>
                                <label for="player-email" class="block text-sm font-medium text-gray-700 mb-1">Player Email</label>
                                <input 
                                    type="email" 
                                    id="player-email" 
                                    name="player-email"
                                    placeholder="player@email.com"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]"
                                >
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="invitation-message" class="block text-sm font-medium text-gray-700 mb-1">Message (Optional)</label>
                                <textarea 
                                    id="invitation-message" 
                                    name="invitation-message"
                                    rows="4"
                                    placeholder="Write a personal message to the player..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F] resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C5F4F] sm:ml-3 sm:w-auto sm:text-sm transition duration-200">
                        Send Invitation
                    </button>
                    <button type="button" 
                            @click="showInviteModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 sm:mt-0 sm:w-auto sm:text-sm transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
