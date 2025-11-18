<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">WELCOME BACK, CLUB MANAGER!</h1>
                        <p class="text-sm text-gray-500">Today: {{ date('F d, Y') }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Bell -->
                        <button class="relative text-gray-600 hover:text-gray-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-0 right-0 inline-block w-2 h-2 bg-red-600 rounded-full"></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-white p-8">
                <!-- Action Buttons -->
                <div class="flex gap-4 mb-8">
                    <a href="{{ route('manager.tournaments') }}" class="bg-[#2C5F4F] text-white hover:bg-[#244D3E] rounded-md px-6 py-3 shadow-md transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        CREATE TOURNAMENT
                    </a>
                    <a href="{{ route('manager.matches') }}" class="bg-[#2C5F4F] text-white hover:bg-[#244D3E] rounded-md px-6 py-3 shadow-md transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        MANAGE MATCHES
                    </a>
                    <a href="{{ route('manager.club') }}" class="bg-[#2C5F4F] text-white hover:bg-[#244D3E] rounded-md px-6 py-3 shadow-md transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        MANAGE CLUB
                    </a>
                </div>

                <!-- Dashboard Sections Grid -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Your Tournaments Section -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Your Tournaments</h2>
                        
                        <!-- Ongoing -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">Ongoing - 3 matches today</h3>
                            <div class="bg-white rounded-md p-4 border border-gray-200">
                                <p class="text-sm text-gray-500">No ongoing tournaments</p>
                            </div>
                        </div>

                        <!-- Upcoming -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">Upcoming</h3>
                            <div class="bg-white rounded-md p-4 border border-gray-200">
                                <p class="text-sm text-gray-500">No upcoming tournaments</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Players Section -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Top Players</h2>
                        <div class="bg-white rounded-md p-4 border border-gray-200">
                            <p class="text-sm text-gray-500">No player data available</p>
                        </div>
                    </div>
                </div>


</body>
</html>