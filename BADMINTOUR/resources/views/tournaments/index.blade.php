<x-dashboard-layout title="Tournaments">
    <div class="max-w-7xl mx-auto" x-data="{ activeTab: 'ongoing' }">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold">ALL TOURNAMENTS</h2>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input 
                    type="text" 
                    placeholder="Search for tournaments"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                    id="tournamentSearch"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b-2 border-[#D4A574] mb-6">
            <div class="flex space-x-8">
                <button 
                    @click="activeTab = 'ongoing'"
                    :class="activeTab === 'ongoing' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'ongoing' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    ONGOING ({{ $ongoingTournaments->count() }})
                </button>
                <button 
                    @click="activeTab = 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'upcoming' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    UPCOMING ({{ $upcomingTournaments->count() }})
                </button>
                <button 
                    @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'completed' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    COMPLETED ({{ $completedTournaments->count() }})
                </button>
                <button 
                    @click="activeTab = 'registered'"
                    :class="activeTab === 'registered' ? 'border-[#C85A54]' : 'border-transparent'"
                    class="px-4 py-3 border-b-2 font-semibold transition"
                    :class="activeTab === 'registered' ? 'text-gray-900' : 'text-gray-600 hover:text-[#C85A54]'"
                >
                    MY TOURNAMENTS ({{ $myTournaments->count() }})
                </button>
            </div>
        </div>

    </div>
</x-dashboard-layout>