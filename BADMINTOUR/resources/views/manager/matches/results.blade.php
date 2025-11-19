<!-- Results Tab -->
<div x-show="activeTab === 'results'" x-cloak>
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold text-black">Completed</h2>
            <div class="flex gap-3 flex-1">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" value="MYSB Tournament 2025" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                </div>
                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    <option>2025-09-19</option>
                    <option>2025-09-20</option>
                    <option>2025-09-21</option>
                </select>
            </div>
        </div>

        <!-- Completed Matches -->
        <div class="space-y-4">
            <!-- Match Card 1 -->
            <div class="bg-white border-2 border-gray-800 rounded-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">MYSB Tournament 2025</p>
                        <h4 class="text-xl font-bold text-black mb-2">Player A vs Player B</h4>
                        <h3 class="text-3xl font-bold text-black mb-3">16 - 21</h3>
                    </div>
                    <span class="text-sm text-gray-600">COURT 1</span>
                </div>
                <div class="flex items-center text-gray-600 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Premiere Southpoint Cabuyao, Laguna</span>
                </div>
            </div>

            <!-- Match Card 2 -->
            <div class="bg-white border-2 border-gray-800 rounded-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">MYSB Tournament 2025</p>
                        <h4 class="text-xl font-bold text-black mb-2">Player C vs Player D</h4>
                        <h3 class="text-3xl font-bold text-black mb-3">21 - 19</h3>
                    </div>
                    <span class="text-sm text-gray-600">COURT 2</span>
                </div>
                <div class="flex items-center text-gray-600 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Premiere Southpoint Cabuyao, Laguna</span>
                </div>
            </div>
        </div>
    </div>
</div>
