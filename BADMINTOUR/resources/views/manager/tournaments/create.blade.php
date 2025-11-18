<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tournament - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ categoryCount: 0, scheduleCount: 1 }">
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
                    <h1 class="text-2xl font-bold text-black">Create Tournament</h1>
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

                    <!-- Category Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-base font-semibold text-black">Category</label>
                            <span class="text-sm text-gray-500" x-text="categoryCount + ' added'"></span>
                        </div>
                        <div class="bg-white border border-gray-300 rounded-md p-4">
                            <div class="grid grid-cols-4 gap-3 mb-4">
                                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <option selected>Men's singles</option>
                                    <option>Women's singles</option>
                                    <option>Men's doubles</option>
                                    <option>Women's doubles</option>
                                    <option>Mixed doubles</option>
                                </select>
                                <input type="text" placeholder="Slots" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <option selected>Skill level</option>
                                    <option>Level A</option>
                                    <option>Level B</option>
                                    <option>Level C</option>
                                    <option>Level D</option>
                                </select>
                                <input type="text" placeholder="Age Requirement" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="categoryCount++" class="bg-[#1B4965] hover:bg-[#143850] text-white px-6 py-2 rounded-md font-medium transition duration-200">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Match Schedules Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-base font-semibold text-black">Match Schedules</label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500" x-text="scheduleCount + ' added'"></span>
                                <button type="button" class="text-gray-600 hover:text-gray-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-300 rounded-md p-4 space-y-4">
                            <div class="grid grid-cols-3 gap-3">
                                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <option selected>Men's singles</option>
                                    <option>Women's singles</option>
                                    <option>Men's doubles</option>
                                    <option>Women's doubles</option>
                                    <option>Mixed doubles</option>
                                </select>
                                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <option selected>First Round</option>
                                    <option>Quarter Finals</option>
                                    <option>Semi Finals</option>
                                    <option>Finals</option>
                                </select>
                                <select class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <option selected>Court #</option>
                                    <option>Court 1</option>
                                    <option>Court 2</option>
                                    <option>Court 3</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="date" placeholder="mm/dd/yy" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                <input type="time" placeholder="--:--:--" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="scheduleCount++" class="bg-[#1B4965] hover:bg-[#143850] text-white px-6 py-2 rounded-md font-medium transition duration-200">
                                    Add Match Schedule
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tournament Fee -->
                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Tournament Fee</label>
                        <input type="text" placeholder="Enter fee (₱)" class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-4">Contact Information</label>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="tel" placeholder="Phone" class="px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            <input type="email" placeholder="Email" class="px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>
                    </div>

                    <!-- Tournament Banner -->
                    <div class="mb-8">
                        <label class="block text-base font-semibold text-black mb-2">Tournament Banner / Logo</label>
                        <div class="border border-gray-300 rounded-md p-4 bg-white">
                            <input type="file" class="w-full">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-4">
                        <button type="button" class="bg-white border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-md font-semibold hover:bg-gray-50 transition duration-200">
                            Save Draft
                        </button>
                        <button type="submit" class="bg-[#1B4965] hover:bg-[#143850] text-white px-8 py-3 rounded-md font-semibold transition duration-200">
                            Create & Post
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>
</html>