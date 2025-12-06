<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Login Type - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-gradient-to-br from-[#1B4965] to-[#2C5F4F]">
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
                <h1 class="text-white text-5xl font-bold mb-3">Choose Login Type</h1>
                <p class="text-white text-lg opacity-90">Select your account type to continue</p>
            </div>

            <!-- Login Type Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Player Login -->
                <a href="{{ route('login') }}" class="group">
                    <div class="bg-white rounded-2xl p-10 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-4 border-transparent hover:border-[#D4A574] h-full">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-[#2C5F4F] to-[#1B4965] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Login as Player</h2>
                            <p class="text-gray-600 mb-6">Access your player account to join tournaments and track rankings</p>
                            <div class="mt-auto">
                                <span class="inline-block bg-[#2C5F4F] text-white px-6 py-2 rounded-lg font-semibold group-hover:bg-[#234A3D] transition">
                                    Player Login →
                                </span>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Manager Login -->
                <a href="{{ route('login.manager') }}" class="group">
                    <div class="bg-white rounded-2xl p-10 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border-4 border-transparent hover:border-[#D4A574] h-full">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-[#7B1F3C] to-[#C85A54] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Login as Manager</h2>
                            <p class="text-gray-600 mb-6">Access your manager account to organize tournaments and manage clubs</p>
                            <div class="mt-auto">
                                <span class="inline-block bg-[#7B1F3C] text-white px-6 py-2 rounded-lg font-semibold group-hover:bg-[#5D1730] transition">
                                    Manager Login →
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Register Link -->
            <div class="text-center mt-10">
                <p class="text-white text-sm">
                    Don't have an account yet? 
                    <a href="{{ route('register') }}" class="text-[#D4A574] hover:text-[#C99564] font-semibold underline">Register here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
