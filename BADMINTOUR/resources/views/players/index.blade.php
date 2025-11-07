<x-dashboard-layout title="Players">
    <div class="max-w-7xl mx-auto">
        <!-- Search Bar -->
        <div class="mb-8">
            <div class="relative">
                <input 
                    type="text" 
                    placeholder="Search for a player"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Players List -->
        <div class="space-y-4">
            <!-- Player Card 1 -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">MANGERMAN</h3>
                        <p class="text-gray-600">CARL</p>
                    </div>
                    <div class="text-right mr-8">
                        <p class="text-sm text-gray-600 font-semibold">SMASHERS CLUB</p>
                    </div>
                    <a href="{{ route('players.show', 1) }}" class="inline-flex items-center text-gray-700 hover:text-gray-900 font-semibold">
                        View details
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Player Card 2 -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">DELA CRUZ</h3>
                        <p class="text-gray-600">KEN</p>
                    </div>
                    <div class="text-right mr-8">
                        <p class="text-sm text-gray-600 font-semibold">PANTHERS CLUB</p>
                    </div>
                    <a href="{{ route('players.show', 2) }}" class="inline-flex items-center text-gray-700 hover:text-gray-900 font-semibold">
                        View details
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Player Card 3 -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">MEGAN</h3>
                        <p class="text-gray-600">HANNS</p>
                    </div>
                    <div class="text-right mr-8">
                        <p class="text-sm text-gray-600 font-semibold">NINJAS CLUB</p>
                    </div>
                    <a href="{{ route('players.show', 3) }}" class="inline-flex items-center text-gray-700 hover:text-gray-900 font-semibold">
                        View details
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
