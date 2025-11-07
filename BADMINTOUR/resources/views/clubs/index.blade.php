<x-dashboard-layout title="Clubs">
    <div class="max-w-7xl mx-auto">
        <!-- Search Bar -->
        <div class="mb-8">
            <div class="relative">
                <input 
                    type="text" 
                    placeholder="Search Clubs"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Your Club Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">YOUR CLUB</h2>
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition cursor-pointer">
                <div class="flex items-center">
                    <img src="https://via.placeholder.com/80" alt="Club Logo" class="w-20 h-20 rounded-full mr-6">
                    <div>
                        <h3 class="text-xl font-bold">My Smasher Badminton</h3>
                        <p class="text-gray-600">Quezon City, Metro Manila</p>
                        <p class="text-gray-600">75 members</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Clubs Section -->
        <div>
            <h2 class="text-2xl font-bold mb-4">ALL CLUBS</h2>
            
            <div class="space-y-4">
                <!-- Club Card 1 -->
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition cursor-pointer">
                    <div class="flex items-center">
                        <img src="https://via.placeholder.com/80" alt="Club Logo" class="w-20 h-20 rounded-full mr-6">
                        <div>
                            <h3 class="text-xl font-bold">Bianca Carlos Badminton Academy</h3>
                            <p class="text-gray-600">Makati City, Metro Manila</p>
                            <p class="text-gray-600">120 members</p>
                        </div>
                    </div>
                </div>

                <!-- Club Card 2 -->
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 hover:shadow-lg transition cursor-pointer">
                    <div class="flex items-center">
                        <img src="https://via.placeholder.com/80" alt="Club Logo" class="w-20 h-20 rounded-full mr-6">
                        <div>
                            <h3 class="text-xl font-bold">My Smasher Badminton</h3>
                            <p class="text-gray-600">Quezon City, Metro Manila</p>
                            <p class="text-gray-600">75 members</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>