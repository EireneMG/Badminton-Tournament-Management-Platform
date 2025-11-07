<x-dashboard-layout>
    <x-slot:title>Notifications</x-slot:title>
    <x-slot:greeting>NOTIFICATIONS</x-slot:greeting>

    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-900">All Notifications</h2>
            <button class="text-sm text-[#1B4965] hover:text-[#2C5F4F] font-semibold transition">
                Mark all as read
            </button>
        </div>

         <!-- Notifications List -->
        <div class="bg-white rounded-lg border-2 border-[#D4A574] overflow-hidden">
            <!-- Notification Item 1 - Unread -->
            <div class="px-6 py-4 hover:bg-gray-50 border-b border-gray-200 transition">
                <div class="flex items-start space-x-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-12 h-12 bg-[#C85A54] rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z"/>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-base font-bold text-gray-900">Tournament Registration Open</p>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-400">2h ago</span>
                                <span class="w-3 h-3 bg-[#C85A54] rounded-full"></span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Registration for MYSB Club Tournament 2025 is now open. Don't miss out!</p>
                    </div>
                </div>
            </div>

            <!-- Notification Item 2 - Unread -->
            <div class="px-6 py-4 hover:bg-gray-50 border-b border-gray-200 transition">
                <div class="flex items-start space-x-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-base font-bold text-gray-900">Match Schedule Updated</p>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-400">5h ago</span>
                                <span class="w-3 h-3 bg-[#C85A54] rounded-full"></span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Your match in Men's Singles has been rescheduled to tomorrow at 10:00 AM.</p>
                    </div>
                </div>
            </div>


</x-dashboard-layout>