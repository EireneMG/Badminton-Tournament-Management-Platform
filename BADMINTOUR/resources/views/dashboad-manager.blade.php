<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __(Club Manager Dashboard') }}
        </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7x1 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Welcome, {{Auth:user()->name }}!</h3>
            <p class="text-gray-600 mb-4">You're logged in as a <span class="font-semibold text-indigo-600">Club Manager</span>.</p>

</div>