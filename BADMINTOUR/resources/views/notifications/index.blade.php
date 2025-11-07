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

            


</x-dashboard-layout>