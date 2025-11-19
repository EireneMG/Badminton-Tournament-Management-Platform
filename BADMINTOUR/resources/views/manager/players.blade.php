<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ 
    showAddPlayerModal: false,
    showViewPlayerModal: false,
    selectedPlayer: {
        id: 'PL001',
        name: 'John Doe',
        email: 'john.doe@email.com',
        club: 'Manila Badminton Club',
        skillLevel: 'A',
        status: 'Active',
        matchesPlayed: 24,
        wins: 20,
        losses: 4,
        joinDate: 'January 15, 2025'
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
                    <h1 class="text-3xl font-bold text-[#2C5F4F]">PLAYERS MANAGEMENT</h1>
                    <div class="flex items-center gap-4">
                        <button class="relative">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8">
                <!-- Search and Filters -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                        <div class="flex-1 w-full lg:w-auto">
                            <input type="text" 
                                   placeholder="Search players by name or skill level" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="">Filter by Skill Level</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="">Filter by Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <button @click="showAddPlayerModal = true" class="bg-[#D4A574] hover:bg-[#C4956A] text-white px-6 py-2 rounded-lg font-semibold transition duration-200 shadow-sm whitespace-nowrap">
                                Add Player
                            </button>
                        </div>
                    </div>
                </div>

                 <!-- Players Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#2C5F4F] text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold">Player ID</th>
                                    <th class="px-6 py-4 text-left font-semibold">Full Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Club</th>
                                    <th class="px-6 py-4 text-left font-semibold">Skill Level</th>
                                    <th class="px-6 py-4 text-left font-semibold">Status</th>
                                    <th class="px-6 py-4 text-left font-semibold">Joined</th>
                                    <th class="px-6 py-4 text-left font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Player 1 -->
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">PL001</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">John Doe</td>
                                    <td class="px-6 py-4 text-gray-600">Manila Badminton Club</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#1B4965] text-white font-bold text-sm">A</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Jan 15, 2025</td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="showViewPlayerModal = true; selectedPlayer = {
                                                id: 'PL001',
                                                name: 'John Doe',
                                                email: 'john.doe@email.com',
                                                club: 'Manila Badminton Club',
                                                skillLevel: 'A',
                                                status: 'Active',
                                                matchesPlayed: 24,
                                                wins: 20,
                                                losses: 4,
                                                joinDate: 'January 15, 2025'
                                            }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                View Profile
                                            </button>
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                 <!-- Player 2 -->
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">PL002</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Maria Santos</td>
                                    <td class="px-6 py-4 text-gray-600">BCBA</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#1B4965] text-white font-bold text-sm">A</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Jan 10, 2025</td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="showViewPlayerModal = true; selectedPlayer = {
                                                id: 'PL002',
                                                name: 'Maria Santos',
                                                email: 'maria.santos@email.com',
                                                club: 'BCBA',
                                                skillLevel: 'A',
                                                status: 'Active',
                                                matchesPlayed: 22,
                                                wins: 18,
                                                losses: 4,
                                                joinDate: 'January 10, 2025'
                                            }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                View Profile
                                            </button>
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Player 3 -->
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">PL003</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Robert Chen</td>
                                    <td class="px-6 py-4 text-gray-600">Laguna Sports Club</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#1B4965] text-white font-bold text-sm">B</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pending</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Jan 20, 2025</td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="showViewPlayerModal = true; selectedPlayer = {
                                                id: 'PL003',
                                                name: 'Robert Chen',
                                                email: 'robert.chen@email.com',
                                                club: 'Laguna Sports Club',
                                                skillLevel: 'B',
                                                status: 'Pending',
                                                matchesPlayed: 18,
                                                wins: 12,
                                                losses: 6,
                                                joinDate: 'January 20, 2025'
                                            }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                View Profile
                                            </button>
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Player 4 -->
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">PL004</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Jennifer Lee</td>
                                    <td class="px-6 py-4 text-gray-600">Quezon City BC</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#1B4965] text-white font-bold text-sm">B</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Dec 28, 2024</td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="showViewPlayerModal = true; selectedPlayer = {
                                                id: 'PL004',
                                                name: 'Jennifer Lee',
                                                email: 'jennifer.lee@email.com',
                                                club: 'Quezon City BC',
                                                skillLevel: 'B',
                                                status: 'Active',
                                                matchesPlayed: 20,
                                                wins: 15,
                                                losses: 5,
                                                joinDate: 'December 28, 2024'
                                            }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                View Profile
                                            </button>
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Player 5 -->
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">PL005</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Michael Tan</td>
                                    <td class="px-6 py-4 text-gray-600">Manila Badminton Club</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#1B4965] text-white font-bold text-sm">C</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Rejected</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Jan 22, 2025</td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="showViewPlayerModal = true; selectedPlayer = {
                                                id: 'PL005',
                                                name: 'Michael Tan',
                                                email: 'michael.tan@email.com',
                                                club: 'Manila Badminton Club',
                                                skillLevel: 'C',
                                                status: 'Rejected',
                                                matchesPlayed: 15,
                                                wins: 8,
                                                losses: 7,
                                                joinDate: 'January 22, 2025'
                                            }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                View Profile
                                            </button>
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Player 6 -->
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600">PL006</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Sarah Johnson</td>
                                    <td class="px-6 py-4 text-gray-600">BCBA</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#1B4965] text-white font-bold text-sm">D</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pending</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Jan 25, 2025</td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button @click="showViewPlayerModal = true; selectedPlayer = {
                                                id: 'PL006',
                                                name: 'Sarah Johnson',
                                                email: 'sarah.johnson@email.com',
                                                club: 'BCBA',
                                                skillLevel: 'D',
                                                status: 'Pending',
                                                matchesPlayed: 10,
                                                wins: 5,
                                                losses: 5,
                                                joinDate: 'January 25, 2025'
                                            }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                View Profile
                                            </button>
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Player Modal -->
    <div x-show="showAddPlayerModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showAddPlayerModal = false"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-8" @click.stop>
                <!-- Close Button -->
                <button @click="showAddPlayerModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-[#2C5F4F]">Add New Player</h2>
                </div>

                <!-- Form -->
                <form class="space-y-6">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                        <input type="text" 
                               placeholder="Enter player's full name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" 
                               placeholder="player@email.com"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                    </div>

                    <!-- Skill Level and Status Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Skill Level -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Skill Level</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="">Select skill level</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="">Select status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
                        <button type="button" @click="showAddPlayerModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Save Player
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>                    