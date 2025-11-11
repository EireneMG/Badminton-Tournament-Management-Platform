@extends('components.dashboard-layout') @section('content')
<div class="min-h-screen bg-gray-50 p-6" x-data="withdrawalRequestData()">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                Request Tournament Withdrawal
            </h1>
            <p class="text-gray-600">
                Submit a withdrawal request for a tournament you've joined. A
                manager will review your request.
            </p>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-lg border-2 border-[#D4A574] p-6">
            <!-- Select Tournament Section -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Select Tournament to Withdraw From</label>

                <!-- Tournament Cards List -->
                <div class="space-y-3">
                    <!-- Tournament 1 - Eligible -->
                    <div 
                        @click="selectTournament('tournament1')"
                        :class="selectedTournament === 'tournament1' ? 'border-[#C85A54] bg-red-50' : 'border-[#D4A574] hover:border-[#C85A54]'"
                        class="border-2 rounded-lg p-4 cursor-pointer transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="font-bold text-gray-900">National Badminton Championship 2025</h3>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold">Eligible</span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span>📅 Jan 15-20, 2025</span>
                                    <span>📍 Metro Sports Complex</span>
                                    <span class="text-[#C85A54] font-semibold">Withdrawal Deadline: Jan 10, 2025</span>
                                </div>
                                <div class="mt-2 text-sm text-gray-700">
                                    <span class="font-semibold">Joined Categories:</span> Men's Singles, Mixed Doubles
                                </div>
                            </div>
                            <div class="ml-4">
                                <input 
                                    type="radio" 
                                    name="tournament" 
                                    :checked="selectedTournament === 'tournament1'"
                                    class="w-5 h-5 text-[#C85A54] focus:ring-[#C85A54]">
                            </div>
                        </div>
                    </div>

                    <!-- Tournament 2 - Eligible -->
                    <div 
                        @click="selectTournament('tournament2')"
                        :class="selectedTournament === 'tournament2' ? 'border-[#C85A54] bg-red-50' : 'border-[#D4A574] hover:border-[#C85A54]'"
                        class="border-2 rounded-lg p-4 cursor-pointer transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="font-bold text-gray-900">City Open Tournament</h3>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold">Eligible</span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span>📅 Feb 5-7, 2025</span>
                                    <span>📍 Central Arena</span>
                                    <span class="text-[#C85A54] font-semibold">Withdrawal Deadline: Jan 30, 2025</span>
                                </div>
                                <div class="mt-2 text-sm text-gray-700">
                                    <span class="font-semibold">Joined Categories:</span> Men's Doubles
                                </div>
                            </div>
                            <div class="ml-4">
                                <input 
                                    type="radio" 
                                    name="tournament" 
                                    :checked="selectedTournament === 'tournament2'"
                                    class="w-5 h-5 text-[#C85A54] focus:ring-[#C85A54]">
                            </div>
                        </div>
                    </div>

                    <!-- Tournament 3 - Deadline Passed -->
                    <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50 opacity-60 cursor-not-allowed">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="font-bold text-gray-900">Summer Smash Tournament</h3>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full font-semibold">Deadline Passed</span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span>📅 Jan 8-10, 2025</span>
                                    <span>📍 Stadium Arena</span>
                                    <span class="text-gray-500 font-semibold">❌ Withdrawal not allowed</span>
                                </div>
                                <div class="mt-2 text-sm text-gray-700">
                                    <span class="font-semibold">Joined Categories:</span> Women's Singles
                                </div>
                            </div>
                            <div class="ml-4">
                                <input 
                                    type="radio" 
                                    name="tournament" 
                                    disabled
                                    class="w-5 h-5 text-gray-400">
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-3">⚠️ Only tournaments with active withdrawal periods are available for selection.</p>
            </div>

            <!-- Reason for Withdrawal -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Withdrawal *</label>
                <textarea 
                    x-model="withdrawalReason"
                    rows="5"
                    placeholder="Please provide a detailed reason for your withdrawal request (e.g., injury, scheduling conflict, personal emergency)..."
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C85A54] focus:border-[#C85A54] resize-none"
                    :disabled="!selectedTournament"></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 20 characters required</p>
            </div>

            <!-- Warning Notice -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Important:</strong> Your withdrawal request will be reviewed by a tournament manager. You will receive a notification once your request is approved or denied. Withdrawal may affect your registration fee eligibility.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between">
                <a href="/tournaments" class="text-gray-600 hover:text-gray-900 font-semibold">
                    ← Back to Tournaments
                </a>
                <div class="flex space-x-3">
                    <button 
                        @click="resetForm()"
                        class="px-6 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Reset
                    </button>
                    <button 
                        @click="submitRequest()"
                        :disabled="!selectedTournament || withdrawalReason.length < 20"
                        :class="!selectedTournament || withdrawalReason.length < 20 ? 'bg-gray-300 cursor-not-allowed' : 'bg-[#C85A54] hover:bg-[#B54A44]'"
                        class="px-6 py-2.5 text-white rounded-lg font-semibold transition">
                        Submit Withdrawal Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
