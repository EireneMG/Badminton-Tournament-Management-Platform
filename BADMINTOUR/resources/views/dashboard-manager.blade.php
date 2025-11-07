<x-dashboard-layout greeting="WELCOME, MANAGER!" title="Manager Dashboard">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tournaments Section -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Manage Tournaments</h2>
            <div class="space-y-4">
                <!-- Placeholder for tournament management -->
                <div class="h-32 bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <p class="text-gray-400">Tournament management will appear here</p>
                </div>
                <div class="h-32 bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <p class="text-gray-400">Tournament management will appear here</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <!-- Placeholder for quick actions -->
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-16 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="lg:col-span-3 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Statistics Overview</h2>
            <div class="grid grid-cols-4 gap-4">
                <div class="h-24 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-24 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-24 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
                <div class="h-24 bg-gray-100 rounded border-2 border-dashed border-gray-300"></div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
