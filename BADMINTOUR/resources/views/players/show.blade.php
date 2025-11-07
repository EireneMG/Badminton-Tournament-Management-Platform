<x-dashboard-layout title="Player Details">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('players.index') }}" class="inline-flex items-center text-gray-700 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold text-lg">PLAYER DETAILS</span>
            </a>
        </div>

        <!-- Player Header -->
        <div class="bg-white rounded-lg p-6 mb-6 border-b-4 border-[#D4A574]">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-sm text-gray-600">CARL</h1>
                    <h2 class="text-3xl font-bold">MANGERMAN</h2>
                </div>
                <div>
                    <p class="text-right font-semibold">SMASHERS CLUB</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b-2 border-[#D4A574] mb-6">
                <div class="flex space-x-8">
                    <button class="px-2 py-3 border-b-2 border-[#C85A54] font-semibold text-gray-900">OVERVIEW</button>
                    <button class="px-2 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54]">MATCHES</button>
                    <button class="px-2 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54]">TOURNAMENTS</button>
                </div>
            </div>

            <!-- Player Stats Grid -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-600 mb-1">AGE</p>
                    <p class="text-lg font-bold">25</p>
                </div>
                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-600 mb-1">HEIGHT</p>
                    <p class="text-lg font-bold">175 cm</p>
                </div>
                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-600 mb-1">HAND</p>
                    <p class="text-lg font-bold">Left</p>
                </div>
                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                    <p class="text-xs text-gray-600 mb-1">REGION</p>
                    <p class="text-lg font-bold">CALABARZON</p>
                </div>
            </div>

            <!-- Rank History -->
            <div>
                <h3 class="text-lg font-bold mb-3">RANK HISTORY</h3>
                <div class="border-2 border-[#D4A574] rounded-lg p-8 bg-gray-50 text-center">
                    <p class="text-gray-400">Rank history chart will appear here</p>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
