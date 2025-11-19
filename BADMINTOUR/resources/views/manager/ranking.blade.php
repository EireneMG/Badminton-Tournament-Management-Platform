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
                        <button class="relative">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <!-- Category Tabs -->
                <div class="mb-6 flex flex-wrap gap-3">
                    <button @click="activeCategory = 'mens-singles'" 
                            :class="activeCategory === 'mens-singles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                            class="px-6 py-2 rounded-lg font-semibold transition duration-200 hover:opacity-90">
                        MEN'S SINGLES
                    </button>
                    <button @click="activeCategory = 'womens-singles'" 
                            :class="activeCategory === 'womens-singles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                            class="px-6 py-2 rounded-lg font-semibold transition duration-200 hover:opacity-90">
                        WOMEN'S SINGLES
                    </button>
                    <button @click="activeCategory = 'mens-doubles'" 
                            :class="activeCategory === 'mens-doubles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                            class="px-6 py-2 rounded-lg font-semibold transition duration-200 hover:opacity-90">
                        MEN'S DOUBLES
                    </button>
                    <button @click="activeCategory = 'womens-doubles'" 
                            :class="activeCategory === 'womens-doubles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                            class="px-6 py-2 rounded-lg font-semibold transition duration-200 hover:opacity-90">
                        WOMEN'S DOUBLES
                    </button>
                    <button @click="activeCategory = 'mixed-doubles'" 
                            :class="activeCategory === 'mixed-doubles' ? 'bg-[#1B4965] text-white' : 'bg-white text-black border-2 border-black'"
                            class="px-6 py-2 rounded-lg font-semibold transition duration-200 hover:opacity-90">
                        MIXED DOUBLES
                    </button>
                </div>

                <!-- Search + Filter Bar -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                        <div class="flex-1 w-full lg:w-auto">
                            <input type="text" 
                                   placeholder="Search player name or club" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="">Filter by Club</option>
                                <option value="manila">Manila Badminton Club</option>
                                <option value="bcba">BCBA</option>
                                <option value="laguna">Laguna Sports Club</option>
                                <option value="quezon">Quezon City BC</option>
                            </select>
                            <button class="bg-[#D4A574] hover:bg-[#C4956A] text-white px-6 py-2 rounded-lg font-semibold transition duration-200 shadow-sm">
                                Export Rankings
                            </button>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</body>
</html>