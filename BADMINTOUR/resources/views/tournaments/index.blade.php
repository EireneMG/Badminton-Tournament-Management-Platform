<x-dashboard-layout title="Tournaments">
    <div class="max-w-7xl mx-auto">
        <!-- Header with Search -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold">ALL TOURNAMENTS</h2>
            <button class="p-2 hover:bg-gray-200 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input 
                    type="text" 
                    placeholder="Search for tournaments"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-400"
                >
                <svg class="w-5 h-5 text-gray-400 absolute right-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b-2 border-[#D4A574] mb-6">
            <div class="flex space-x-8">
                <button class="px-4 py-3 border-b-2 border-[#C85A54] font-semibold text-gray-900">ONGOING</button>
                <button class="px-4 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54]">UPCOMING</button>
                <button class="px-4 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54]">COMPLETED</button>
            </div>
        </div>

        <!-- Tournament Cards -->
        <div class="space-y-4">
            <!-- Tournament Card 1 -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                <h3 class="text-xl font-bold mb-3">Philippine Intercollegiate Badminton Championships</h3>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm">October 5-7, 2025</span>
                    </div>
                    
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm">Centro Atletico Badminton Court, Cubao, QC</span>
                    </div>
                    
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm">Organizer: Philippine Badminton Association (PBAD)</span>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button class="bg-[#C85A54] text-white px-6 py-2 rounded-md hover:bg-[#B54A44] transition font-semibold">
                        Register now
                    </button>
                </div>
            </div>

            <!-- Tournament Card 2 -->
            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                <h3 class="text-xl font-bold mb-3">Badminton Laguna Invitational Tournament</h3>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm">October 14, 2025</span>
                    </div>
                    
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm">Premier Southpoint Cabuyao, Laguna</span>
                    </div>
                    
                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm">Organizer: Powerworld Raven</span>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button class="bg-[#C85A54] text-white px-6 py-2 rounded-md hover:bg-[#B54A44] transition font-semibold">
                        Register now
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
