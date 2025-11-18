<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Tournament - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ autoSchedule: true }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center">
                    <a href="/manager/tournaments" class="mr-4 text-gray-600 hover:text-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-black">Generate Tournament</h1>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <form class="max-w-4xl">
                    <!-- Tournament Name -->
                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Tournament Name</label>
                        <input type="text" placeholder="Enter tournament name" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    </div>

                    <!-- Venue Row -->
                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Venue</label>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" placeholder="Enter Venue" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            <input type="text" placeholder="Enter # of courts" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>
                    </div>

                    <!-- Dates Row -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-base font-semibold text-black mb-2">Start Date</label>
                            <input type="date" placeholder="mm/dd/yy" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label class="block text-base font-semibold text-black mb-2">End Date</label>
                            <input type="date" placeholder="mm/dd/yy" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Description / Overview</label>
                        <textarea rows="5" placeholder="Write a short overview of the tournament" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]"></textarea>
                    </div>

                    <!-- Category (Auto-Generated) -->
                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-4">Category (Auto-Generated)</label>
                        <div class="space-y-3">
                            <!-- Men's Singles -->
                            <div class="bg-white border border-gray-300 rounded-md p-4">
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">Men's singles</div>
                                    <input type="text" placeholder="Slots" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                        <option selected>Open</option>
                                        <option>Level A</option>
                                        <option>Level B</option>
                                        <option>Level C</option>
                                        <option>Level D</option>
                                    </select>
                                    <input type="text" placeholder="16+" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>

                            <!-- Women's Singles -->
                            <div class="bg-white border border-gray-300 rounded-md p-4">
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">Women's singles</div>
                                    <input type="text" placeholder="Slots" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                        <option selected>Open</option>
                                        <option>Level A</option>
                                        <option>Level B</option>
                                        <option>Level C</option>
                                        <option>Level D</option>
                                    </select>
                                    <input type="text" placeholder="16+" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>

                            <!-- Men's Doubles -->
                            <div class="bg-white border border-gray-300 rounded-md p-4">
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">Men's doubles</div>
                                    <input type="text" placeholder="Slots" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                        <option selected>Open</option>
                                        <option>Level A</option>
                                        <option>Level B</option>
                                        <option>Level C</option>
                                        <option>Level D</option>
                                    </select>
                                    <input type="text" placeholder="16+" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>

                            <!-- Women's Doubles -->
                            <div class="bg-white border border-gray-300 rounded-md p-4">
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">Women's doubles</div>
                                    <input type="text" placeholder="Slots" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                        <option selected>Open</option>
                                        <option>Level A</option>
                                        <option>Level B</option>
                                        <option>Level C</option>
                                        <option>Level D</option>
                                    </select>
                                    <input type="text" placeholder="16+" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>

                            <!-- Mixed Doubles -->
                            <div class="bg-white border border-gray-300 rounded-md p-4">
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">Mixed doubles</div>
                                    <input type="text" placeholder="Slots" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                        <option selected>Open</option>
                                        <option>Level A</option>
                                        <option>Level B</option>
                                        <option>Level C</option>
                                        <option>Level D</option>
                                    </select>
                                    <input type="text" placeholder="16+" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>
                        </div>
                    </div>

                    
    </div>
</body>
</html>