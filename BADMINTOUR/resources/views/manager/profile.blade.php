<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Manager Profile - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ 
    showAddHandlerModal: false,
    showEditProfileModal: false,
    showChangePasswordModal: false,
    showPassword: false,
    showNewPassword: false,
    showConfirmPassword: false,
    showSuccessToast: false
}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <div class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-black">CLUB MANAGER PROFILE</h1>
                    <div class="flex items-center gap-4">
                        <button class="relative">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

             <!-- Content Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <div class="max-w-5xl mx-auto space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg border-2 border-black p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-black">Personal Information</h2>
                            <button @click="showEditProfileModal = true" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                Edit Profile
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-600">Full Name: <span class="text-black font-semibold">Jake Peralta</span></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Gender: <span class="text-black font-semibold">Male</span></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Email: <span class="text-black font-semibold">JP.smashersclub@gmail.com</span></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Contact No: <span class="text-black font-semibold">0917-123-4567</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Club Information -->
                    <div class="bg-white rounded-lg border-2 border-black p-6">
                        <h2 class="text-2xl font-bold text-black mb-6">Club Information</h2>
                        <div class="flex items-center justify-between">
                            <div class="space-y-2">
                                <p class="text-black font-semibold text-lg">Smashers Club</p>
                                <p class="text-gray-600">75 members</p>
                            </div>
                            <button class="bg-white hover:bg-gray-50 text-black px-6 py-2 rounded-lg border-2 border-black font-semibold transition duration-200">
                                View Club
                            </button>
                        </div>
                    </div>

                     <!-- Managers / Handlers -->
                    <div class="bg-white rounded-lg border-2 border-black p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-black">Managers / Handlers</h2>
                            <button @click="showAddHandlerModal = true" class="bg-white hover:bg-gray-50 text-black px-6 py-2 rounded-lg border-2 border-black font-semibold transition duration-200">
                                Add Handler
                            </button>
                        </div>
                        <div class="min-h-[150px] flex items-center justify-center text-gray-400">
                            <p>No handlers added yet</p>
                        </div>
                    </div>

                     <!-- Change Password -->
                    <div class="bg-white rounded-lg border-2 border-black p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-black">Security</h2>
                            <button @click="showChangePasswordModal = true" class="bg-[#D4A574] hover:bg-[#C4956A] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                Change Password
                            </button>
                        </div>
                        <p class="text-gray-600">Last password change: <span class="text-black font-semibold">Never</span></p>
                    </div>

                    <!-- Logins -->
                    <div class="bg-white rounded-lg border-2 border-black p-6">
                        <h2 class="text-2xl font-bold text-black mb-6">Logins</h2>
                        <div class="min-h-[150px] flex items-center justify-center text-gray-400">
                            <p>No login history available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast Notification -->
    <div x-show="showSuccessToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         @click="showSuccessToast = false"
         x-init="$watch('showSuccessToast', value => { if(value) setTimeout(() => showSuccessToast = false, 3000) })"
         class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg cursor-pointer">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-semibold">Profile updated successfully!</span>
        </div>
    </div>