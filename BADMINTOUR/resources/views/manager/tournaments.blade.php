<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ activeTab: 'ongoing' }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b-2 border-black px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-black">ALL TOURNAMENTS</h1>
                    </div>
                    <button class="text-gray-600 hover:text-gray-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <!-- Action Buttons -->
                <div class="flex gap-4 mb-6">
                    <a href="/manager/tournaments/create" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-8 py-3 rounded-md font-semibold transition duration-200 uppercase">
                        Create Tournament
                    </a>
                    <a href="/manager/tournaments/generate" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-8 py-3 rounded-md font-semibold transition duration-200 uppercase">
                        Generate Tournament
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="mb-6">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" placeholder="Search for tournaments" class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    </div>
                </div>

                 <!-- Tabs -->
                <div class="border-b border-gray-300 mb-6">
                    <div class="flex space-x-8">
                        <button 
                            @click="activeTab = 'ongoing'" 
                            :class="activeTab === 'ongoing' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Ongoing
                        </button>
                        <button 
                            @click="activeTab = 'upcoming'" 
                            :class="activeTab === 'upcoming' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Upcoming
                        </button>
                        <button 
                            @click="activeTab = 'completed'" 
                            :class="activeTab === 'completed' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Completed
                        </button>
                        <button 
                            @click="activeTab = 'your'" 
                            :class="activeTab === 'your' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Your Tournaments
                        </button>
                    </div>
                </div>

                 <!-- Tournament Cards -->
                <div class="space-y-4">
                    <!-- Ongoing Tournaments -->
                    <div x-show="activeTab === 'ongoing'" x-cloak>
                        <!-- Tournament Card 1 -->
                        <div class="bg-white border-2 border-black rounded-lg p-6 hover:shadow-md transition duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-black mb-2">14th Alabang Badminton Invitational</h3>
                                    <p class="text-gray-700">Alabang Smashers vs Bayanan Club</p>
                                </div>
                                <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded-md font-medium transition duration-200">
                                    View tournament
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Tournaments -->
                    <div x-show="activeTab === 'upcoming'" x-cloak>
                        <!-- Tournament Card 1 -->
                        <div class="bg-white border-2 border-black rounded-lg p-6 hover:shadow-md transition duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-black mb-2">Smash-it Tournament 2025</h3>
                                </div>
                                <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded-md font-medium transition duration-200">
                                    Request to Join
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Tournaments -->
                    <div x-show="activeTab === 'completed'" x-cloak>
                        <!-- Tournament Card 1 -->
                        <div class="bg-white border-2 border-black rounded-lg p-6 hover:shadow-md transition duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-black mb-2">MYSB Tournament 2025</h3>
                                </div>
                                <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded-md font-medium transition duration-200">
                                    View Tournament
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Your Tournaments -->
                    <div x-show="activeTab === 'your'" x-cloak>
                        <!-- Tournament Card 1 -->
                        <div class="bg-white border-2 border-black rounded-lg p-6 hover:shadow-md transition duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-black mb-2">Smash For a Cause</h3>
                                    <p class="text-gray-500 text-sm mt-2">Posted 3 hours ago</p>
                                </div>
                                <button class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-2 rounded-md font-medium transition duration-200">
                                    Invite a club
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
