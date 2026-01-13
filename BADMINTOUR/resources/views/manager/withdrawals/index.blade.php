<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Requests - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-black">WITHDRAWAL REQUESTS</h1>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if(!$club)
                    <div class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No Club Yet</h3>
                        <p class="text-gray-500 mb-6 text-center max-w-md">Create your club first to manage withdrawal requests.</p>
                        <a href="{{ route('manager.create-club') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Create Your Club
                        </a>
                    </div>
                @elseif($withdrawalRequests->count() === 0)
                    <div class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No Pending Withdrawal Requests</h3>
                        <p class="text-gray-500 text-center max-w-md">All withdrawal requests have been processed.</p>
                    </div>
                @else
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">Pending Withdrawal Requests</h2>
                            <p class="text-sm text-gray-600 mt-1">Review and process player withdrawal requests</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tournament</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($withdrawalRequests as $withdrawalRequest)
                                        @php
                                            $registration = $withdrawalRequest->tournamentRegistration;
                                            $player = $registration->player;
                                            $tournament = $registration->tournament;
                                            $category = $registration->category;
                                        @endphp
                                        @php
                                            $partner = $registration->partner;
                                            $isDoublesCategory = $category && (
                                                str_contains(strtolower($category->name), 'doubles') || 
                                                str_contains(strtolower($category->name), 'mixed')
                                            );
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $player->first_name }} {{ $player->last_name }}
                                                    @if($partner)
                                                        <br><span class="text-gray-600 text-xs">& Partner: {{ $partner->first_name }} {{ $partner->last_name }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $player->email }}</div>
                                                @if($isDoublesCategory && $partner)
                                                <div class="text-xs text-yellow-600 mt-1">
                                                    ⚠️ Team withdrawal - both players will be withdrawn
                                                </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $tournament->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $category->full_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                                    {{ $withdrawalRequest->reason }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $withdrawalRequest->created_at->diffForHumans() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('manager.withdrawals.show', $withdrawalRequest->id) }}" 
                                                   class="text-[#2C5F4F] hover:text-[#244D3E] mr-4">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
</body>
</html>

