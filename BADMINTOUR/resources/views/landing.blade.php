<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badminton Tournament Management Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-gradient-to-br from-[#1B4965] to-[#2C5F4F]">
    <div class="h-full flex items-center justify-center p-8">
        <div class="max-w-4xl w-full">
            <!-- Platform Title -->
            <div class="text-center mb-12">
                <h1 class="text-white text-6xl font-bold mb-4">
                    Badminton Tournament<br>Management Platform
                </h1>
                <p class="text-white text-xl opacity-90">
                    Manage tournaments, track rankings, and connect with the badminton community
                </p>
            </div>

            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                <!-- Register Card -->
                <a href="/register" class="group">
                    <div class="bg-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border-4 border-transparent hover:border-[#D4A574]">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-20 h-20 bg-[#7B1F3C] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Register</h2>
                            <p class="text-gray-600">Create a new account as a player or manager</p>
                        </div>
                    </div>
                </a>

                <!-- Login Card -->
                <a href="/login-select" class="group">
                    <div class="bg-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border-4 border-transparent hover:border-[#D4A574]">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-20 h-20 bg-[#2C5F4F] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Login</h2>
                            <p class="text-gray-600">Access your existing account</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Footer -->
            <div class="text-center mt-12">
                <p class="text-white text-sm opacity-75">
                    © 2025 Badminton Tournament Management Platform. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
