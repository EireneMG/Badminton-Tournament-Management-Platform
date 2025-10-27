<x-app-layout>
    <x-slot name="header">
        <h2 class="front-semibold text-xl text-gray-800 leading-tight">
            {{ _key: 'Player Dashboard' }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7x1 mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text lg front-semibold mb-4">Welcome, {{ Auth::user()->name }}</h3>
                    <p class="text-gray-600 mb-4">You're logged in as a <span class=" front-semibold text-indigo-600">Player</span></p>

                    <div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>