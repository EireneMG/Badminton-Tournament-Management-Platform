<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Club Manager Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 mb-4">You're logged in as a <span class="font-semibold text-indigo-600">Club Manager</span>.</p>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-indigo-900 mb-2">Manage Players</h4>
                            <p class="text-sm text-indigo-700">View and manage all players in your club.</p>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-900 mb-2">Team Management</h4>
                            <p class="text-sm text-red-700">Create teams, assign players, and set lineups.</p>
                        </div>
                        
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-900 mb-2">Schedule Events</h4>
                            <p class="text-sm text-orange-700">Schedule matches, training sessions, and events.</p>
                        </div>
                        
                        <div class="bg-teal-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-teal-900 mb-2">Reports & Analytics</h4>
                            <p class="text-sm text-teal-700">View club statistics and player performance reports.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
