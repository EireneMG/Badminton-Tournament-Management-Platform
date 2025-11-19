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

     <!-- Edit Profile Modal -->
    <div x-show="showEditProfileModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showEditProfileModal = false"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-8" @click.stop>
                <!-- Close Button -->
                <button @click="showEditProfileModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-[#2C5F4F]">Edit Profile Information</h2>
                </div>

                <!-- Form -->
                <form @submit.prevent="showEditProfileModal = false; showSuccessToast = true" class="space-y-6">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                        <input type="text" 
                               value="Jake Peralta"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                    </div>

                    <!-- Email and Contact Number Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" 
                                   value="JP.smashersclub@gmail.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                            <input type="text" 
                                   value="0917-123-4567"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                        </div>
                    </div>

                    <!-- Gender and Club Name Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                <option value="male" selected>Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Club Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Club Name</label>
                            <input type="text" 
                                   value="Smashers Club"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
                        <button type="button" @click="showEditProfileModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div x-show="showChangePasswordModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showChangePasswordModal = false"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-8" @click.stop>
                <!-- Close Button -->
                <button @click="showChangePasswordModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-[#D4A574]">Change Password</h2>
                    <p class="text-gray-600 mt-2">Ensure your password is strong and secure</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="showChangePasswordModal = false; showSuccessToast = true" class="space-y-6">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" 
                                   placeholder="Enter current password"
                                   class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A574] focus:border-transparent">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <div class="relative">
                            <input :type="showNewPassword ? 'text' : 'password'" 
                                   placeholder="Enter new password"
                                   class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A574] focus:border-transparent">
                            <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" 
                                   placeholder="Re-enter new password"
                                   class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A574] focus:border-transparent">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
                        <button type="button" @click="showChangePasswordModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#D4A574] hover:bg-[#C4956A] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>