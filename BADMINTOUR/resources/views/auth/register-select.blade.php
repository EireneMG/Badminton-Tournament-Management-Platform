<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Registration Type - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-gradient-to-br from-[#1B4965] to-[#2C5F4F]" x-data="{ showManagerWarning: false }">
    <div class="h-full flex items-center justify-center p-8">
        <div class="max-w-5xl w-full">
            <!-- Back to Landing -->
            <div class="mb-8">
                <a href="/" class="inline-flex items-center text-white hover:text-[#D4A574] transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Home
                </a>
            </div>

            <!-- Title -->
            <div class="text-center mb-12">
                <h1 class="text-white text-5xl font-bold mb-3">Choose Registration Type</h1>
                <p class="text-white text-lg opacity-90">Select how you want to join the platform</p>
            </div>

             <!-- Registration Type Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Player Registration -->
                <a href="/register/player" class="group">
                    <div class="bg-white rounded-2xl p-10 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-4 border-transparent hover:border-[#D4A574] h-full">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-[#2C5F4F] to-[#1B4965] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Register as Player</h2>
                            <p class="text-gray-600 mb-6">Join tournaments, track your rankings, and compete with other players</p>
                            <ul class="text-left text-sm text-gray-700 space-y-2">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-[#2C5F4F] mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Participate in tournaments
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-[#2C5F4F] mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    View rankings and stats
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-[#2C5F4F] mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Join badminton clubs
                                </li>
                            </ul>
                        </div>
                    </div>
                </a>

                <!-- Manager Registration -->
                <div @click="showManagerWarning = true" class="group cursor-pointer">
                    <div class="bg-white rounded-2xl p-10 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-4 border-transparent hover:border-[#D4A574] h-full">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-[#7B1F3C] to-[#C85A54] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Register as Manager</h2>
                            <p class="text-gray-600 mb-6">Manage your club and host tournaments for the badminton community</p>
                            <ul class="text-left text-sm text-gray-700 space-y-2">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-[#7B1F3C] mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Create and manage tournaments
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-[#7B1F3C] mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Manage club members
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-[#7B1F3C] mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Review registrations & payments
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>