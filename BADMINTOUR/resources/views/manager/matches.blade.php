<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ 
    activeCategory: 'mens-singles',
    activeTab: 'overview',
    lagunaOngoing: false,
    bcbaOngoing: true,
    lagunaBrackets: false,
    bcbaBrackets: true
}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-black">MATCH MANAGEMENT</h1>
                    <button class="text-gray-600 hover:text-gray-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <!-- Category Buttons -->
                <div class="flex gap-3 mb-6 flex-wrap">
                    <button 
                        @click="activeCategory = 'mens-singles'"
                        :class="activeCategory === 'mens-singles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                        class="px-6 py-2 rounded-md font-semibold uppercase text-sm transition duration-200 hover:bg-[#1B4965] hover:text-white">
                        Men's Singles
                    </button>
                    <button 
                        @click="activeCategory = 'womens-singles'"
                        :class="activeCategory === 'womens-singles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                        class="px-6 py-2 rounded-md font-semibold uppercase text-sm transition duration-200 hover:bg-[#1B4965] hover:text-white">
                        Women's Singles
                    </button>
                    <button 
                        @click="activeCategory = 'mens-doubles'"
                        :class="activeCategory === 'mens-doubles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                        class="px-6 py-2 rounded-md font-semibold uppercase text-sm transition duration-200 hover:bg-[#1B4965] hover:text-white">
                        Men's Doubles
                    </button>
                    <button 
                        @click="activeCategory = 'womens-doubles'"
                        :class="activeCategory === 'womens-doubles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                        class="px-6 py-2 rounded-md font-semibold uppercase text-sm transition duration-200 hover:bg-[#1B4965] hover:text-white">
                        Women's Doubles
                    </button>
                    <button 
                        @click="activeCategory = 'mixed-doubles'"
                        :class="activeCategory === 'mixed-doubles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                        class="px-6 py-2 rounded-md font-semibold uppercase text-sm transition duration-200 hover:bg-[#1B4965] hover:text-white">
                        Mixed Doubles
                    </button>
                </div>

                <!-- Tabs -->
                 <div class="border-b border-gray-300 mb-6">
                    <div class="flex space-x-8">
                        <button 
                            @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Overview
                        </button>
                        <button 
                            @click="activeTab = 'ongoing'" 
                            :class="activeTab === 'ongoing' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Ongoing
                        </button>
                        <button 
                            @click="activeTab = 'brackets'" 
                            :class="activeTab === 'brackets' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Brackets
                        </button>
                        <button 
                            @click="activeTab = 'schedule'" 
                            :class="activeTab === 'schedule' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Schedule
                        </button>
                        <button 
                            @click="activeTab = 'results'" 
                            :class="activeTab === 'results' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                            class="pb-3 px-2 uppercase text-sm transition duration-200">
                            Results
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                @include('manager.matches.overview')
                @include('manager.matches.ongoing')
                @include('manager.matches.brackets')
                @include('manager.matches.schedule')
                @include('manager.matches.results')
            </main>
        </div>
    </div>
</body>
</html>

