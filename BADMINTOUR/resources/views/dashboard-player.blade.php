<x-dashboard-layout greeting="WELCOME, PLAYER!" title="Player Dashboard">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tournaments Section -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Tournaments</h2>
            <div class="space-y-4">
                <!-- Placeholder for tournament items -->
                <div class="h-32 bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <p class="text-gray-400">Tournament items will appear here</p>
                </div>
                <div class="h-32 bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <p class="text-gray-400">Tournament items will appear here</p>
                </div>
                <div class="h-32 bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <p class="text-gray-400">Tournament items will appear here</p>
                </div>
            </div>
        </div>

        <!-- Top Players Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Top Players</h2>
            <div class="space-y-3">
                <!-- Placeholder for top players -->
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
            </div>
        </div>

        <!-- Top Clubs Section -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Top Clubs</h2>
            <div class="space-y-3">
                <!-- Placeholder for top clubs -->
                <div class="h-20 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-20 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-20 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
