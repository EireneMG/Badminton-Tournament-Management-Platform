<!-- Overview Tab -->
<div x-show="activeTab === 'overview'" x-cloak>
    <!-- Ongoing Matches Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-black mb-4 flex items-center">
            <span class="w-3 h-3 bg-red-500 rounded-full mr-3"></span>
            Ongoing Matches
        </h2>
        <div class="space-y-4">
            <!-- Tournament Card 1 -->
            <div class="bg-white border-2 border-red-500 rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-bold text-black mb-2">Laguna Invitation 2025</h3>
                        <p class="text-red-500 font-semibold mb-2"><span class="font-bold">2 matches</span> in progress</p>
                        <p class="text-gray-600 text-sm">Started: 2025-09-20 • 2:00 pm</p>
                    </div>
                    <button class="bg-[#1B4965] hover:bg-[#143850] text-white px-6 py-2 rounded-md font-medium transition duration-200">
                        Update Scores
                    </button>
                </div>
            </div>

            <!-- Tournament Card 2 -->
            <div class="bg-white border-2 border-red-500 rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-bold text-black mb-2">BCBA Tournament 2025</h3>
                        <p class="text-red-500 font-semibold mb-2"><span class="font-bold">2 matches</span> in progress</p>
                        <p class="text-gray-600 text-sm">Started: 2025-09-20 • 2:00 pm</p>
                    </div>
                    <button class="bg-[#1B4965] hover:bg-[#143850] text-white px-6 py-2 rounded-md font-medium transition duration-200">
                        Update Scores
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Upcoming Section -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-black mb-4">Upcoming</h2>
        <div class="space-y-4">
            <!-- Match Card 1 -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-black mb-1">Player C vs Player D</h3>
                        <p class="text-gray-600 text-sm">Laguna Invitation 2025</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-1">Court 1</p>
                        <p class="text-sm text-gray-600">2025-09-20 • 2 pm</p>
                    </div>
                </div>
            </div>

            <!-- Empty Card -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 h-24"></div>

            <div class="flex justify-end">
                <button class="bg-[#D4A574] hover:bg-[#C49564] text-white px-6 py-2 rounded-md font-medium transition duration-200">
                    View all
                </button>
            </div>
        </div>
    </div>
</div>