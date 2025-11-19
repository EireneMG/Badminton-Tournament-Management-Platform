<!-- Schedule Tab -->
<div x-show="activeTab === 'schedule'" x-cloak>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold text-black">Upcoming</h2>
            <div class="flex gap-3">
                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    <option>2025-09-20</option>
                    <option>2025-09-21</option>
                    <option>2025-09-22</option>
                </select>
                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    <option>Time</option>
                    <option>Morning</option>
                    <option>Afternoon</option>
                    <option>Evening</option>
                </select>
            </div>
        </div>

        <!-- Laguna Invitation 2025 -->
        <div class="mb-6" x-data="{ expanded: true }">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-black">Laguna Invitation 2025</h3>
                <button @click="expanded = !expanded" class="text-black">
                    <svg :class="expanded ? 'rotate-180' : ''" class="w-6 h-6 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div x-show="expanded" x-cloak class="space-y-4">
                <!-- Match Card 1 -->
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-xl font-bold text-black">Player E vs Player F</h4>
                                <span class="text-sm text-gray-600 ml-4">COURT 1</span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm mb-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Premiere Southpoint Cabuyao, Laguna</span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>5 PM</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Match Card 2 -->
                <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-xl font-bold text-black">Player G vs Player H</h4>
                                <span class="text-sm text-gray-600 ml-4">COURT 2</span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm mb-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Premiere Southpoint Cabuyao, Laguna</span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>5 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BCBA Tournament 2025 -->
        <div x-data="{ expanded: false }">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-black">BCBA Tournament 2025</h3>
                <button @click="expanded = !expanded" class="text-black">
                    <svg :class="expanded ? 'rotate-180' : ''" class="w-6 h-6 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
