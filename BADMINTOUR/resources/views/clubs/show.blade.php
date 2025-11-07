<x-dashboard-layout title="Club Profile">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('clubs.index') }}" class="inline-flex items-center text-gray-700 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold text-lg">My Club</span>
            </a>
        </div>

        <!-- Club Header -->
        <div class="bg-white rounded-lg p-6 mb-6 border-b-4 border-[#D4A574]">
            <div class="flex items-center mb-6">
                <img src="https://via.placeholder.com/100" alt="Club Logo" class="w-24 h-24 rounded-full mr-6">
                <div>
                    <h1 class="text-2xl font-bold">My Smasher Badminton</h1>
                    <p class="text-gray-600">Quezon City, Metro Manila</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- Announcement -->
                <div>
                    <h3 class="text-lg font-bold mb-3">Announcement</h3>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <p class="text-gray-600">none</p>
                    </div>
                </div>

                <!-- Members Count -->
                <div>
                    <h3 class="text-lg font-bold mb-3"># members</h3>
                    <div class="border rounded-lg p-4 bg-gray-50 flex items-center justify-center">
                        <span class="text-5xl font-bold">75</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Event -->
            <div class="mt-6">
                <h3 class="text-lg font-bold mb-3">Upcoming Event</h3>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-gray-900 rounded-full mr-2"></span>
                        <span>Club Tournament</span>
                    </div>
                    <span class="font-semibold">October 15</span>
                </div>
            </div>
        </div>

        <!-- Members List -->
        <div class="bg-white rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Members</h3>
            
            <div class="space-y-3">
                <!-- Member Card 1 -->
                <div class="border-2 border-[#D4A574] rounded-lg p-4 flex items-center hover:bg-gray-50 cursor-pointer">
                    <img src="https://via.placeholder.com/50" alt="Player" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <p class="font-semibold">Player 1</p>
                        <p class="text-sm text-gray-600">Rank 2</p>
                    </div>
                </div>

                <!-- Member Card 2 -->
                <div class="border-2 border-[#D4A574] rounded-lg p-4 flex items-center hover:bg-gray-50 cursor-pointer">
                    <img src="https://via.placeholder.com/50" alt="Player" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <p class="font-semibold">Player 2</p>
                        <p class="text-sm text-gray-600">Rank 3</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
