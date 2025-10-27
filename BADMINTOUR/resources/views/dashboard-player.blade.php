<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __(key:'Player Dashboard')}}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 mb-4">You're logged in as a <span class="font-semibold text-indigo-600">Player</span>.</p>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-900 mb-2">My Stats</h4>
                            <p class="text-sm text-blue-700">View your performance statistics and achievements.</p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-900 mb-2">My Teams</h4>
                            <p class="text-sm text-green-700">Manage your team memberships and schedules.</p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-900 mb-2">Training Sessions</h4>
                            <p class="text-sm text-purple-700">Book and view upcoming training sessions.</p>
                        </div>
                        
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-yellow-900 mb-2">Messages</h4>
                            <p class="text-sm text-yellow-700">Check messages from your club managers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
