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