<x-dashboard-layout title="Profile">
    <div class="max-w-5xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg p-8 mb-6 border-b-4 border-[#D4A574]">
            <div class="flex items-start">
                <!-- Profile Avatar -->
                <div class="mr-8">
                    <div class="w-32 h-32 bg-gray-300 rounded-full flex items-center justify-center">
                        <svg class="w-20 h-20 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="flex-1">
                    <h1 class="text-3xl font-bold mb-1">CARL MANGERMAN</h1>
                    <p class="text-gray-600 mb-1">@MangyCarl99</p>
                    <p class="text-sm text-gray-500 mb-1">Email: carlmangerman@gmail.com</p>
                    <p class="text-sm text-gray-500 mb-4">Player ID: #001</p>
                    
                    <div class="inline-flex items-center space-x-2 text-sm">
                        <span class="bg-[#C85A54] text-white px-3 py-1 rounded font-semibold">RANK #1</span>
                        <span class="font-semibold">•</span>
                        <span class="font-semibold">MEN'S SINGLES</span>
                        <span class="font-semibold">•</span>
                        <span class="font-semibold">8700 POINTS</span>
                    </div>
                </div>

                <!-- Club Badge -->
                <div class="text-right">
                    <p class="font-bold">SMASHERS CLUB</p>
                </div>
            </div>
        </div>

        <!-- Personal & Club Information -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg p-6 border-l-4 border-[#D4A574]">
                <h2 class="text-xl font-bold mb-4">PERSONAL INFORMATION</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Full Name:</p>
                        <p class="font-semibold">Carl Mangerman</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Age:</p>
                        <p class="font-semibold">25</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Height:</p>
                        <p class="font-semibold">175 cm</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Gender:</p>
                        <p class="font-semibold">Male</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Date of Birth:</p>
                        <p class="font-semibold">October 9, 1999</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Contact Number:</p>
                        <p class="font-semibold">09260567891</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Address:</p>
                        <p class="font-semibold">402 Matalino Street, Quezon City</p>
                    </div>
                </div>
            </div>

            <!-- Club Information -->
            <div class="bg-white rounded-lg p-6 border-l-4 border-[#D4A574]">
                <h2 class="text-xl font-bold mb-4">CLUB INFORMATION</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Club:</p>
                        <p class="font-semibold">Smashers Club</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Skill level:</p>
                        <p class="font-semibold">B</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Position:</p>
                        <p class="font-semibold">Singles</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Member since:</p>
                        <p class="font-semibold">2025</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Player Statistics -->
        <div class="bg-white rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">PLAYER STATISTICS</h2>
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-[#C85A54] text-white rounded-lg p-6 text-center">
                    <p class="text-sm mb-2">MATCHES PLAYED</p>
                    <p class="text-4xl font-bold">40</p>
                </div>
                <div class="bg-[#C85A54] text-white rounded-lg p-6 text-center">
                    <p class="text-sm mb-2">WINS</p>
                    <p class="text-4xl font-bold">30</p>
                </div>
                <div class="bg-[#C85A54] text-white rounded-lg p-6 text-center">
                    <p class="text-sm mb-2">LOSSES</p>
                    <p class="text-4xl font-bold">10</p>
                </div>
                <div class="bg-[#C85A54] text-white rounded-lg p-6 text-center">
                    <p class="text-sm mb-2">WIN RATE</p>
                    <p class="text-4xl font-bold">60%</p>
                </div>
            </div>
        </div>

        <!-- Match History Tracking -->
        <div class="bg-white rounded-lg p-6 mb-6 border-2 border-[#D4A574]">
            <h2 class="text-xl font-bold mb-4">Match History Tracking</h2>
            <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-400">Match history data will appear here</p>
            </div>
        </div>

        <div class="flex justify-end mb-6">
            <button class="bg-[#D4A574] text-white px-8 py-2 rounded-md hover:bg-[#C49664] transition font-semibold">
                View more
            </button>
        </div>

        <!-- Tournament History -->
        <div class="bg-white rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">TOURNAMENT HISTORY</h2>
            <div class="h-48 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-400">Tournament history will appear here</p>
            </div>
        </div>

        <!-- Ranking History -->
        <div class="bg-white rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">RANKING HISTORY</h2>
            <div class="h-48 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-400">Ranking history chart will appear here</p>
            </div>
        </div>
    </div>
</x-dashboard-layout>
