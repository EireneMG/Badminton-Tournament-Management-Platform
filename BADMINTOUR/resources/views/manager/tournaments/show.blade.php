<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tournament->name }} - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        @include('layouts.manager-sidebar')

        <div class="flex-1 flex flex-col overflow-hidden" x-data="{ activeTab: 'overview' }">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('manager.tournaments') }}" class="mr-4 text-gray-600 hover:text-gray-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-black">{{ $tournament->name }}</h1>
                            <span class="text-sm px-2 py-1 rounded-full 
                                @if($tournament->status === 'draft') bg-gray-200 text-gray-700
                                @elseif($tournament->status === 'published') bg-blue-100 text-blue-700
                                @elseif($tournament->status === 'ongoing') bg-green-100 text-green-700
                                @elseif($tournament->status === 'completed') bg-purple-100 text-purple-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($tournament->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        @if($isOwner)
                            @if($tournament->status === 'draft')
                                <a href="{{ route('manager.tournaments.edit', $tournament) }}" class="bg-white border-2 border-gray-300 text-gray-700 px-4 py-2 rounded-md font-semibold hover:bg-gray-50 transition duration-200">
                                    Edit Tournament
                                </a>
                                <form action="{{ route('manager.tournaments.publish', $tournament) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-[#2C5F4F] hover:bg-[#234b3f] text-white px-4 py-2 rounded-md font-semibold transition duration-200">
                                        Publish Tournament
                                    </button>
                                </form>
                            @elseif($tournament->status === 'published')
                                <form action="{{ route('manager.tournaments.generate-matches', $tournament) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-[#1B4965] hover:bg-[#143850] text-white px-4 py-2 rounded-md font-semibold transition duration-200">
                                        Generate Matches
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="flex space-x-1 mt-4 border-b border-gray-200">
                    <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-b-2 border-[#2C5F4F] text-[#2C5F4F]' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-2 font-medium text-sm transition">
                        OVERVIEW
                    </button>
                    <a href="{{ route('manager.matches', ['tournament' => $tournament->id]) }}" class="px-4 py-2 font-medium text-sm text-gray-500 hover:text-gray-700 transition border-b-2 border-transparent hover:border-gray-300">
                        MATCHES
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div x-show="activeTab === 'overview'">
                    <div class="grid grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Venue</h3>
                            <p class="text-lg font-semibold text-black">{{ $tournament->venue_name }}</p>
                            <p class="text-sm text-gray-600">{{ $tournament->number_of_courts }} courts</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Tournament Dates</h3>
                            <p class="text-lg font-semibold text-black">{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-600">Registration: until {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d') }}</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Entry Fee</h3>
                            <p class="text-lg font-semibold text-black">₱{{ number_format($tournament->tournament_fee, 2) }}</p>
                            <p class="text-sm text-gray-600">per category</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-black mb-4">Categories</h3>
                            @forelse($tournament->categories as $category)
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                                    <div>
                                        <span class="font-medium">
                                            @switch($category->type)
                                                @case('MS') Men's Singles @break
                                                @case('WS') Women's Singles @break
                                                @case('MD') Men's Doubles @break
                                                @case('WD') Women's Doubles @break
                                                @case('XD') Mixed Doubles @break
                                                @default {{ $category->type }}
                                            @endswitch
                                        </span>
                                        @if($category->skill_level_requirements)
                                            <span class="text-sm text-gray-500 ml-2">({{ $category->skill_level_requirements }})</span>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $category->slots }} slots</span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No categories defined</p>
                            @endforelse
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-black mb-4">Contact Information</h3>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $tournament->contact_email }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>{{ $tournament->contact_phone }}</span>
                                </div>
                                @if($tournament->facebook_link)
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        <a href="{{ $tournament->facebook_link }}" target="_blank" class="text-blue-600 hover:underline">Facebook/Messenger</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($tournament->description)
                        <div class="bg-white rounded-lg shadow p-6 mb-8">
                            <h3 class="text-lg font-semibold text-black mb-4">Description</h3>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $tournament->description }}</p>
                        </div>
                    @endif

                    <!-- Tournament Dates and Deadlines -->
                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <h3 class="text-lg font-semibold text-black mb-4">Important Dates</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Tournament Dates</span>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</p>
                            </div>
                            @if($tournament->registration_deadline)
                            <div>
                                <span class="text-sm text-gray-500">Registration Deadline</span>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d, Y h:i A') }}</p>
                            </div>
                            @endif
                            @if($tournament->withdrawal_deadline)
                            <div>
                                <span class="text-sm text-gray-500">Withdrawal Deadline</span>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($tournament->withdrawal_deadline)->format('M d, Y h:i A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Player Requested Withdrawal Section -->
                    @if($isOwner)
                    @php
                        $withdrawalRequests = \App\Models\WithdrawalRequest::whereIn('tournament_registration_id', function($query) use ($tournament) {
                            $query->select('id')
                                ->from('tournament_registrations')
                                ->where('tournament_id', $tournament->id);
                        })
                        ->where('status', 'pending')
                        ->with(['tournamentRegistration.player', 'tournamentRegistration.partner', 'tournamentRegistration.category'])
                        ->get();
                    @endphp
                    @if($withdrawalRequests->count() > 0)
                    <div class="bg-white rounded-lg shadow p-6 mb-8 border-l-4 border-yellow-400">
                        <h3 class="text-lg font-semibold text-black mb-4">Player Requested Withdrawal</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-yellow-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player(s)</th>
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
                                            $category = $registration->category;
                                            $isDoublesCategory = $category && (
                                                str_contains(strtolower($category->name), 'doubles') || 
                                                str_contains(strtolower($category->name), 'mixed')
                                            );
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $registration->player->first_name }} {{ $registration->player->last_name }}
                                                    @if($registration->partner)
                                                        <br><span class="text-gray-600">& {{ $registration->partner->first_name }} {{ $registration->partner->last_name }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $category->name ?? 'N/A' }}</div>
                                                @if($isDoublesCategory && $registration->partner)
                                                    <div class="text-xs text-yellow-700 font-medium">(Team Withdrawal)</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-600 max-w-md">{{ $withdrawalRequest->reason }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $withdrawalRequest->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    <form action="{{ route('manager.withdrawals.approve', $withdrawalRequest) }}" method="POST" 
                                                          onsubmit="return confirm('Payment refunds are handled outside the system. Ensure you have refunded the player before confirming withdrawal. Continue?');" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium transition">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('manager.withdrawals.reject', $withdrawalRequest) }}" method="POST" 
                                                          onsubmit="return confirm('Are you sure you want to reject this withdrawal request?');" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium transition">
                                                            Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    @endif

                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-black">Player Registrations</h3>
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">{{ $tournament->registrations->count() }} total</span>
                                <span class="text-sm px-2 py-1 rounded-full bg-green-100 text-green-700">
                                    {{ $tournament->registrations->where('status', 'approved')->count() }} approved
                                </span>
                                <span class="text-sm px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                    {{ $tournament->registrations->whereIn('status', ['pending', 'eligible', 'awaiting_payment', 'paid'])->count() }} pending
                                </span>
                            </div>
                        </div>
                        
                        @if($tournament->registrations->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partner</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eligibility</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($tournament->registrations as $registration)
                                            @php
                                                // Check eligibility status
                                                $eligibilityService = app(\App\Services\EligibilityService::class);
                                                $eligibilityCheck = $eligibilityService->checkEligibility(
                                                    $registration->player,
                                                    $registration->category,
                                                    $registration->partner
                                                );
                                                $eligibilityStatus = $eligibilityCheck['eligible'] ? 'Passed' : 'Failed';
                                            @endphp
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        @if($registration->player->profile_photo)
                                                            <img src="{{ Storage::url($registration->player->profile_photo) }}" alt="{{ $registration->player->first_name }}" class="h-10 w-10 rounded-full object-cover mr-3">
                                                        @else
                                                            <div class="h-10 w-10 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white font-semibold text-sm mr-3">
                                                                {{ strtoupper(substr($registration->player->first_name ?? 'U', 0, 1) . substr($registration->player->last_name ?? '', 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">{{ $registration->player->first_name ?? 'Unknown' }} {{ $registration->player->last_name ?? '' }}</div>
                                                            <a href="{{ route('players.show', $registration->player) }}" class="text-xs text-[#2C5F4F] hover:underline">View Profile</a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $registration->player->email ?? '' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @switch($registration->category->type ?? '')
                                                            @case('MS') Men's Singles @break
                                                            @case('WS') Women's Singles @break
                                                            @case('MD') Men's Doubles @break
                                                            @case('WD') Women's Doubles @break
                                                            @case('XD') Mixed Doubles @break
                                                            @default {{ $registration->category->type ?? 'N/A' }}
                                                        @endswitch
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if($registration->partner)
                                                            {{ $registration->partner->first_name }} {{ $registration->partner->last_name }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        @if($eligibilityStatus === 'Passed') bg-green-100 text-green-700
                                                        @else bg-red-100 text-red-700
                                                        @endif">
                                                        {{ $eligibilityStatus }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        @if($registration->status === 'approved') bg-green-100 text-green-700
                                                        @elseif($registration->status === 'withdrawn') bg-gray-100 text-gray-700
                                                        @elseif($registration->status === 'withdrawal_requested') bg-yellow-100 text-yellow-700
                                                        @elseif($registration->status === 'rejected') bg-red-100 text-red-700
                                                        @else bg-yellow-100 text-yellow-700
                                                        @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($isOwner && $tournament->status === 'upcoming')
                                                        @if($registration->status !== 'approved' && $registration->status !== 'rejected' && $registration->status !== 'withdrawn' && $registration->status !== 'withdrawal_requested')
                                                            <div class="flex items-center gap-2">
                                                                @if($registration->status !== 'paid')
                                                                    <form action="{{ route('manager.registrations.mark-paid', $registration) }}" method="POST" class="inline" onsubmit="return confirm('Payment will be marked. After marking payment, you must approve the registration to finalize it. Continue?')">
                                                                        @csrf
                                                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-medium transition">
                                                                            Mark as Paid
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <form action="{{ route('manager.registrations.update-status', $registration) }}" method="POST" class="inline">
                                                                        @csrf
                                                                        <input type="hidden" name="status" value="approved">
                                                                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#234b3f] text-white px-3 py-1.5 rounded text-xs font-medium transition">
                                                                            Approve
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                <form action="{{ route('manager.registrations.update-status', $registration) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this registration?')">
                                                                    @csrf
                                                                    <input type="hidden" name="status" value="rejected">
                                                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-medium transition">
                                                                        Reject
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @elseif($registration->status === 'withdrawal_requested')
                                                            <span class="text-xs text-yellow-600 italic">Withdrawal Pending</span>
                                                        @else
                                                            <span class="text-xs text-gray-500 italic">
                                                                @if($registration->status === 'approved') Approved
                                                                @elseif($registration->status === 'rejected') Rejected
                                                                @elseif($registration->status === 'withdrawn') Withdrawn
                                                                @else {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                    @elseif(!$isOwner)
                                                        <span class="text-xs text-gray-500 italic">View Only</span>
                                                    @else
                                                        <span class="text-xs text-gray-500 italic">
                                                            @if($registration->status === 'approved') Approved
                                                            @elseif($registration->status === 'rejected') Rejected
                                                            @elseif($registration->status === 'withdrawn') Withdrawn
                                                            @else {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-gray-500">No registrations yet</p>
                                <p class="text-sm text-gray-400 mt-1">Players can register once the tournament is published</p>
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
