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

                <!-- Rankings Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#2C5F4F] text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold">Rank</th>
                                    <th class="px-6 py-4 text-left font-semibold">Player Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Club</th>
                                    <th class="px-6 py-4 text-left font-semibold">Matches Played</th>
                                    <th class="px-6 py-4 text-left font-semibold">Wins</th>
                                    <th class="px-6 py-4 text-left font-semibold">Losses</th>
                                    <th class="px-6 py-4 text-left font-semibold">Points</th>
                                    <th class="px-6 py-4 text-left font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rank 1 -->
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#D4A574] text-white font-bold">1</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">John Doe</td>
                                    <td class="px-6 py-4 text-gray-600">Manila Badminton Club</td>
                                    <td class="px-6 py-4 text-gray-600">24</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">20</td>
                                    <td class="px-6 py-4 text-red-600 font-semibold">4</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">2850</td>
                                    <td class="px-6 py-4">
                                        <button @click="showPlayerModal = true; selectedPlayer = {
                                            name: 'John Doe',
                                            club: 'Manila Badminton Club',
                                            rank: 1,
                                            points: 2850,
                                            matchesPlayed: 24,
                                            wins: 20,
                                            losses: 4,
                                            winRate: 83.3
                                        }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                            View Stats
                                        </button>
                                    </td>
                                </tr>
                                <!-- Rank 2 -->
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400 text-white font-bold">2</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Maria Santos</td>
                                    <td class="px-6 py-4 text-gray-600">BCBA</td>
                                    <td class="px-6 py-4 text-gray-600">22</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">18</td>
                                    <td class="px-6 py-4 text-red-600 font-semibold">4</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">2720</td>
                                    <td class="px-6 py-4">
                                        <button @click="showPlayerModal = true; selectedPlayer = {
                                            name: 'Maria Santos',
                                            club: 'BCBA',
                                            rank: 2,
                                            points: 2720,
                                            matchesPlayed: 22,
                                            wins: 18,
                                            losses: 4,
                                            winRate: 81.8
                                        }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                            View Stats
                                        </button>
                                    </td>
                                </tr>
                                <!-- Rank 3 -->
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400 text-white font-bold">3</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Robert Chen</td>
                                    <td class="px-6 py-4 text-gray-600">Laguna Sports Club</td>
                                    <td class="px-6 py-4 text-gray-600">26</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">19</td>
                                    <td class="px-6 py-4 text-red-600 font-semibold">7</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">2650</td>
                                    <td class="px-6 py-4">
                                        <button @click="showPlayerModal = true; selectedPlayer = {
                                            name: 'Robert Chen',
                                            club: 'Laguna Sports Club',
                                            rank: 3,
                                            points: 2650,
                                            matchesPlayed: 26,
                                            wins: 19,
                                            losses: 7,
                                            winRate: 73.1
                                        }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                            View Stats
                                        </button>
                                    </td>
                                </tr>
                                <!-- Rank 4 -->
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-white font-bold">4</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Jennifer Lee</td>
                                    <td class="px-6 py-4 text-gray-600">Quezon City BC</td>
                                    <td class="px-6 py-4 text-gray-600">20</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">15</td>
                                    <td class="px-6 py-4 text-red-600 font-semibold">5</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">2580</td>
                                    <td class="px-6 py-4">
                                        <button @click="showPlayerModal = true; selectedPlayer = {
                                            name: 'Jennifer Lee',
                                            club: 'Quezon City BC',
                                            rank: 4,
                                            points: 2580,
                                            matchesPlayed: 20,
                                            wins: 15,
                                            losses: 5,
                                            winRate: 75.0
                                        }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                            View Stats
                                        </button>
                                    </td>
                                </tr>
                                <!-- Rank 5 -->
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-white font-bold">5</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Michael Tan</td>
                                    <td class="px-6 py-4 text-gray-600">Manila Badminton Club</td>
                                    <td class="px-6 py-4 text-gray-600">23</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">16</td>
                                    <td class="px-6 py-4 text-red-600 font-semibold">7</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">2520</td>
                                    <td class="px-6 py-4">
                                        <button @click="showPlayerModal = true; selectedPlayer = {
                                            name: 'Michael Tan',
                                            club: 'Manila Badminton Club',
                                            rank: 5,
                                            points: 2520,
                                            matchesPlayed: 23,
                                            wins: 16,
                                            losses: 7,
                                            winRate: 69.6
                                        }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                            View Stats
                                        </button>
                                    </td>
                                </tr>
                                <!-- Rank 6 -->
                                <tr class="bg-gray-50 border-b border-gray-200 hover:bg-gray-100 transition duration-150">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-white font-bold">6</span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">Sarah Johnson</td>
                                    <td class="px-6 py-4 text-gray-600">BCBA</td>
                                    <td class="px-6 py-4 text-gray-600">21</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">14</td>
                                    <td class="px-6 py-4 text-red-600 font-semibold">7</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">2450</td>
                                    <td class="px-6 py-4">
                                        <button @click="showPlayerModal = true; selectedPlayer = {
                                            name: 'Sarah Johnson',
                                            club: 'BCBA',
                                            rank: 6,
                                            points: 2450,
                                            matchesPlayed: 21,
                                            wins: 14,
                                            losses: 7,
                                            winRate: 66.7
                                        }" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                            View Stats
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>