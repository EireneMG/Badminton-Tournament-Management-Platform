<x-dashboard-layout title="Ranking">
    <div class="max-w-7xl mx-auto">
        <!-- Search and Filters -->
        <div class="flex items-center justify-between mb-6">
            <div class="relative flex-1 max-w-md">
                <input 
                    type="text" 
                    placeholder="Search player/region"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <div class="flex items-center space-x-4">
                <div>
                    <label class="text-sm text-gray-600 mr-2">Week</label>
                    <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none">
                        <option>Week 10 (08-26-2025)</option>
                        <option>Week 9 (08-19-2025)</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-sm text-gray-600 mr-2">Per Page</label>
                    <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none">
                        <option>1</option>
                        <option>10</option>
                        <option>25</option>
                    </select>
                </div>
                
                <button class="p-2 hover:bg-gray-200 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </button>
            </div>
        </div>

         <!-- Category Tabs -->
        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-1 mb-6 inline-flex space-x-1">
            <button class="bg-[#C85A54] text-white px-6 py-2 rounded font-semibold hover:bg-[#B54A44]">MEN'S SINGLES</button>
            <button class="bg-white text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-100">WOMEN'S SINGLES</button>
            <button class="bg-white text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-100">MEN'S DOUBLES</button>
            <button class="bg-white text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-100">WOMEN'S DOUBLES</button>
            <button class="bg-white text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-100">MIXED DOUBLES</button>
        </div>