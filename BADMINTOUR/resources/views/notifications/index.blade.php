@php $isManager = auth()->user()->role === 'manager'; @endphp

@if($isManager)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        @include('layouts.manager-sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-[#2C5F4F]">Notifications</h1>
                        <p class="text-sm text-gray-500">All notifications for your manager account</p>
                    </div>
                    @if($notifications->whereNull('read_at')->count() > 0)
                    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-[#1B4965] hover:text-[#2C5F4F] font-semibold transition">
                            Mark all as read
                        </button>
                    </form>
                    @endif
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <div class="max-w-5xl mx-auto">
                    @include('notifications.partials.list', ['notifications' => $notifications])

                    @if($notifications->hasPages())
                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</body>
</html>
@else
<x-dashboard-layout>
    <x-slot:title>Notifications</x-slot:title>
    <x-slot:greeting>NOTIFICATIONS</x-slot:greeting>

    <div class="max-w-4xl mx-auto">
        @include('notifications.partials.list', ['notifications' => $notifications])

        @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</x-dashboard-layout>
@endif
