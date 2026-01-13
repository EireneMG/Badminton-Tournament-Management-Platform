<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tournament->name }} - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        @include('layouts.manager-sidebar')

        <div class="flex-1 flex flex-col overflow-hidden" x-data="{ 
            activeTab: 'overview',
            showConfirm: false,
            confirmMessage: '',
            confirmType: 'default',
            confirmFormId: null,
            rejectReason: '',
            showToast: false,
            toastMessage: '',
            toastType: 'success',
            showRescheduleModal: false,
            rescheduleStartDate: '{{ $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('Y-m-d') : '' }}',
            rescheduleEndDate: '{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('Y-m-d') : '' }}',
            openRescheduleModal() {
                this.rescheduleStartDate = '{{ $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('Y-m-d') : '' }}';
                this.rescheduleEndDate = '{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('Y-m-d') : '' }}';
                this.showRescheduleModal = true;
            },
            closeRescheduleModal() {
                this.showRescheduleModal = false;
            },
            openConfirm(message, type, formId) {
                this.confirmMessage = message;
                this.confirmType = type || 'default';
                this.confirmFormId = formId;
                this.rejectReason = '';
                this.showConfirm = true;
            },
            closeConfirm() {
                this.showConfirm = false;
                this.confirmMessage = '';
                this.confirmType = 'default';
                this.confirmFormId = null;
                this.rejectReason = '';
            },
            executeConfirm() {
                if (this.confirmType === 'reject-withdrawal') {
                    if (!this.rejectReason || this.rejectReason.trim().length < 5) {
                        this.showToastMessage('Please provide a rejection reason (at least 5 characters).', 'error');
                        return;
                    }
                    if (this.confirmFormId) {
                        const form = document.getElementById(this.confirmFormId);
                        if (form) {
                            form.querySelector('input[name=manager_response]').value = this.rejectReason;
                            form.submit();
                        }
                    }
                } else if (this.confirmFormId) {
                    const form = document.getElementById(this.confirmFormId);
                    if (form) {
                        form.submit();
                    }
                }
                this.closeConfirm();
            },
            showToastMessage(message, type) {
                this.toastMessage = message;
                this.toastType = type || 'success';
                this.showToast = true;
                setTimeout(() => {
                    this.showToast = false;
                }, 4000);
            }
        }">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                {{-- Host Notice for Tournament Owner --}}
                @if($isOwner)
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-bold text-green-800">You are the TOURNAMENT HOST</p>
                            <p class="text-sm text-green-700">You will manage approvals, rejections, withdrawals, match results, walkovers, and all rescheduling actions.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Dual Meet Invited Notice --}}
                @if(isset($isDualMeetInvited) && $isDualMeetInvited)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-4 rounded-r-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-amber-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-bold text-amber-800">Dual Meet Tournament - View Only</p>
                            <p class="text-sm text-amber-700">You were invited to this Dual Meet. This tournament is hosted by <strong>{{ $hostClubName }}</strong>. All approvals, results, and administrative actions will be handled by the Host Manager.</p>
                        </div>
                    </div>
                </div>
                @endif

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
                                @if($tournament->status === 'published') bg-blue-100 text-blue-700
                                @elseif($tournament->status === 'ongoing') bg-green-100 text-green-700
                                @elseif($tournament->status === 'completed') bg-purple-100 text-purple-700
                                @elseif($tournament->status === 'upcoming') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($tournament->status) }}
                            </span>
                            @if($tournament->is_dual_meet)
                                <span class="ml-2 text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">Dual Meet</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-3">
                        @if($isOwner)
                            @if(in_array($tournament->status, ['published', 'upcoming']))
                                <form action="{{ route('manager.tournaments.generate-matches', $tournament) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-[#1B4965] hover:bg-[#143850] text-white px-4 py-2 rounded-md font-semibold transition duration-200">
                                        Generate Matches
                                    </button>
                                </form>
                                
                                <button 
                                    type="button"
                                    @click="openRescheduleModal()"
                                    class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md font-semibold transition duration-200">
                                    Reschedule Tournament
                                </button>
                            @endif
                            
                            @if(isset($canDelete) && $canDelete)
                                <form action="{{ route('manager.tournaments.destroy', $tournament) }}" method="POST" class="inline" id="delete-tournament-form" onsubmit="return confirm('Are you sure you want to delete this tournament? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-semibold transition duration-200">
                                        Delete Tournament
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
                    <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-b-2 border-[#2C5F4F] text-[#2C5F4F]' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-2 font-medium text-sm transition">
                        MATCH HISTORY
                    </button>
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
                            <p class="text-lg font-semibold text-black">{{ $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('M d') : 'N/A' }} - {{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') : 'N/A' }}</p>
                            <p class="text-sm text-gray-600">Registration: until {{ $tournament->registration_deadline ? \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d') : 'N/A' }}</p>
                        </div>
                        @php
                            $fee = $tournament->tournament_fee;
                            if (is_null($fee) || $fee === '' || $fee == 0) {
                                $fee = $tournament->registration_fee ?? 0;
                            }
                        @endphp
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Entry Fee</h3>
                            <p class="text-lg font-semibold text-black">₱{{ number_format($fee, 2) }}</p>
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
                                            {{ $category->full_name }}
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
                                                <div class="text-sm text-gray-900">{{ $category->full_name ?? 'N/A' }}</div>
                                                @if($isDoublesCategory && $registration->partner)
                                                    <div class="text-xs text-yellow-700 font-medium mt-1">
                                                        ⚠️ Approving will withdraw both players
                                                    </div>
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
                                                    <form id="approve-withdrawal-form-{{ $withdrawalRequest->id }}" action="{{ route('manager.withdrawals.approve', $withdrawalRequest) }}" method="POST" class="inline" style="display: none;">
                                                        @csrf
                                                        <input type="hidden" name="manager_response" value="">
                                                    </form>
                                                    <button type="button" @click="openConfirm('Approve this withdrawal request? Payment refunds are handled outside the system. You must return the tournament fee to the player before confirming.', 'default', 'approve-withdrawal-form-{{ $withdrawalRequest->id }}')" style="background-color: #16a34a; color: #ffffff;"
                                                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium transition">
                                                        Approve
                                                    </button>
                                                    <form id="reject-withdrawal-form-{{ $withdrawalRequest->id }}" action="{{ route('manager.withdrawals.reject', $withdrawalRequest) }}" method="POST" class="inline" style="display: none;">
                                                        @csrf
                                                        <input type="hidden" name="manager_response" value="">
                                                    </form>
                                                    <button type="button" @click="openConfirm('Please provide a reason for rejecting this withdrawal request. This message will be sent to the player.', 'reject-withdrawal', 'reject-withdrawal-form-{{ $withdrawalRequest->id }}')" style="background-color: #dc2626; color: #ffffff;"
                                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium transition">
                                                        Reject
                                                    </button>
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

                    <!-- Player Registrations Section (for Upcoming and Ongoing tournaments) - Shows only approved, non-withdrawn players -->
                    @if(in_array($tournament->status, ['upcoming', 'ongoing', 'published']) && $tournament->categories->count() > 0)
                    @php
                        $firstCategoryId = $tournament->categories->first()?->id;
                    @endphp
                    <div class="bg-white rounded-lg shadow p-6 mb-8" x-data="{ selectedRegCategoryId: {{ $firstCategoryId ?? 'null' }} }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-black">Player Registrations</h3>
                            @if($tournament->categories->count() > 1)
                                <div class="w-64">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                                    <select x-model="selectedRegCategoryId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                        @foreach($tournament->categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        
                        @php
                            $hasRegistrations = false;
                            foreach ($tournament->categories as $category) {
                                if (isset($registrationsByCategory[$category->id]) && $registrationsByCategory[$category->id]->count() > 0) {
                                    $hasRegistrations = true;
                                    break;
                                }
                            }
                        @endphp
                        
                        @if($hasRegistrations)
                            @foreach($tournament->categories as $category)
                                @if(isset($registrationsByCategory[$category->id]) && $registrationsByCategory[$category->id]->count() > 0)
                                    <div class="mb-6" x-show="selectedRegCategoryId == {{ $category->id }}" x-cloak>
                                        <h4 class="text-md font-semibold text-[#2C5F4F] mb-3">{{ $category->full_name }}</h4>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partner</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($registrationsByCategory[$category->id] as $registration)
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm font-medium text-gray-900">
                                                                    {{ $registration->player->first_name }} {{ $registration->player->last_name }}
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm text-gray-900">{{ $category->full_name }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm text-gray-900">
                                                                    @if($registration->partner)
                                                                        {{ $registration->partner->first_name }} {{ $registration->partner->last_name }}
                                                                    @else
                                                                        <span class="text-gray-400">—</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <p class="text-gray-500 text-center py-4">No players registered yet.</p>
                        @endif
                    </div>
                    @endif
                    
                    <!-- All Registrations Management Table (for managers) -->
                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-black">All Registrations</h3>
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
                                                        {{ $registration->category->full_name ?? 'N/A' }}
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
                                                        @if(in_array($registration->id, $pendingWithdrawalRequestIds ?? [])) bg-yellow-100 text-yellow-700
                                                        @elseif($registration->status === 'approved') bg-green-100 text-green-700
                                                        @elseif($registration->status === 'withdrawn') bg-gray-100 text-gray-700
                                                        @elseif($registration->status === 'rejected') bg-red-100 text-red-700
                                                        @else bg-yellow-100 text-yellow-700
                                                        @endif">
                                                        @if(in_array($registration->id, $pendingWithdrawalRequestIds ?? []))
                                                            Withdrawal Requested
                                                        @else
                                                            {{ ucfirst(str_replace('_', ' ', $registration->status)) }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($isOwner && in_array($tournament->status, ['published', 'upcoming']))
                                                        @if($registration->status !== 'approved' && $registration->status !== 'rejected' && $registration->status !== 'withdrawn' && !in_array($registration->id, $pendingWithdrawalRequestIds ?? []))
                                                            <div class="flex items-center gap-2">
                                                                <form id="approve-registration-form-{{ $registration->id }}" action="{{ route('manager.registrations.update-status', $registration) }}" method="POST" class="inline" style="display: none;">
                                                                    @csrf
                                                                    <input type="hidden" name="status" value="approved">
                                                                </form>
                                                                <button type="button" @click="openConfirm('Payment is handled outside the system. Confirm player is already paid before approving. Approve this registration?', 'default', 'approve-registration-form-{{ $registration->id }}')" class="bg-[#2C5F4F] hover:bg-[#234b3f] text-white px-3 py-1.5 rounded text-xs font-medium transition">
                                                                    Approve
                                                                </button>
                                                                    <form id="reject-registration-form-{{ $registration->id }}" action="{{ route('manager.registrations.update-status', $registration) }}" method="POST" class="inline" style="display: none;">
                                                                        @csrf
                                                                        <input type="hidden" name="status" value="rejected">
                                                                    </form>
                                                                    <button type="button" @click="openConfirm('Are you sure you want to reject this registration? The player will be notified of the rejection.', 'default', 'reject-registration-form-{{ $registration->id }}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-medium transition">
                                                                        Reject
                                                                    </button>
                                                            </div>
                                                        @elseif(in_array($registration->id, $pendingWithdrawalRequestIds ?? []))
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

                <div x-show="activeTab === 'history'" x-cloak>
                    @php
                        $firstCatWithMatches = $tournament->categories->first(function($cat) use ($matchesByCategory) {
                            return isset($matchesByCategory[$cat->id]) && count($matchesByCategory[$cat->id]) > 0;
                        });
                        $defaultHistoryCat = $firstCatWithMatches?->id ?? ($tournament->categories->first()->id ?? null);
                    @endphp
                    <div class="bg-white rounded-lg shadow p-6" x-data="{ selectedCategoryId: {{ $defaultHistoryCat ?? 'null' }} }">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-black">Match History</h2>
                            @if($tournament->categories->count() > 1)
                                <div class="w-64">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                                    <select x-model="selectedCategoryId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                        @foreach($tournament->categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-6">
                            @foreach($tournament->categories as $category)
                                <div x-show="selectedCategoryId == {{ $category->id }}" x-cloak class="space-y-4">
                                    <h3 class="text-lg font-semibold text-[#2C5F4F]">{{ $category->full_name }}</h3>

                                    @if($tournament->bracket_type === 'round_robin' && isset($standingsByCategory[$category->id]) && count($standingsByCategory[$category->id]) > 0)
                                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                            <div class="bg-[#2C5F4F] px-4 py-3">
                                                <h4 class="text-lg font-bold text-white">Standings</h4>
                                            </div>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Rank</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Player/Team</th>
                                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">W</th>
                                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">L</th>
                                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Sets W</th>
                                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Sets L</th>
                                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase">Points</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach($standingsByCategory[$category->id] as $standing)
                                                            <tr class="{{ $loop->iteration <= 3 ? 'bg-green-50' : '' }}">
                                                                <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ $standing['rank'] ?? $loop->iteration }}</td>
                                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                                    {{ $standing['team_name'] ?? $standing['player_name'] }}
                                                                </td>
                                                                <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $standing['wins'] ?? 0 }}</td>
                                                                <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $standing['losses'] ?? 0 }}</td>
                                                                <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $standing['sets_won'] ?? 0 }}</td>
                                                                <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $standing['sets_lost'] ?? 0 }}</td>
                                                                <td class="px-4 py-3 text-sm text-center text-gray-700">{{ ($standing['points_for'] ?? 0) }} - {{ ($standing['points_against'] ?? 0) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($matchesByCategory[$category->id]) && count($matchesByCategory[$category->id]) > 0)
                                        @php
                                            $rounds = array_keys($matchesByCategory[$category->id]);
                                            // Normalize round naming to a consistent progression
                                            $normalize = function ($r) {
                                                $r = strtolower(trim($r));
                                                if (str_contains($r, 'round of 32')) return 'Round of 32';
                                                if ($r === 'round 1' || str_starts_with($r, 'round 1') || str_contains($r, 'first round')) return 'Round 1';
                                                if (str_contains($r, 'round of 16') || preg_match('/round\s+2\b/i', $r)) return 'Round of 16';
                                                if (str_contains($r, 'round of 8') || str_contains($r, 'quarter')) return 'Quarterfinals';
                                                if (str_contains($r, 'semi')) return 'Semifinals';
                                                if (str_contains($r, 'final')) return 'Finals';
                                                if (preg_match('/round\s+(\d+)/i', $r, $m)) return 'Round ' . $m[1];
                                                return $r;
                                            };
                                            $order = function($r) {
                                                $r = strtolower(trim($r));
                                                if (str_contains($r, 'round of 32')) return 10;
                                                if ($r === 'round 1' || str_starts_with($r, 'round 1') || str_contains($r, 'first round')) return 20;
                                                if (str_contains($r, 'round of 16') || preg_match('/round\s+2\b/i', $r)) return 30;
                                                if (str_contains($r, 'round of 8') || str_contains($r, 'quarter')) return 40;
                                                if (str_contains($r, 'semi')) return 50;
                                                if (str_contains($r, 'final')) return 60;
                                                if (preg_match('/round\s+(\d+)/i', $r, $m)) return 20 + (int)$m[1];
                                                return 999;
                                            };
                                            usort($rounds, fn($a,$b) => $order($a) <=> $order($b));
                                        @endphp

                                        @php
                                            $displayRound = function ($roundName) {
                                                $r = strtolower(trim((string) $roundName));
                                                if ($r === '1' || str_starts_with($r, 'round 1')) return 'Round 1';
                                                if ($r === '2' || str_contains($r, 'quarter')) return 'Quarterfinals';
                                                if ($r === '3' || str_contains($r, 'semi')) return 'Semifinals';
                                                if ($r === '4' || str_contains($r, 'final')) return 'Finals';
                                                if (preg_match('/round\s+(\d+)/i', $r, $m)) return 'Round ' . $m[1];
                                                return ucfirst($roundName);
                                            };
                                        @endphp
                                        @foreach($rounds as $roundName)
                                            <div class="border border-gray-200 rounded-lg">
                                                <div class="bg-gray-50 px-4 py-2 font-semibold text-gray-800">{{ $displayRound($roundName) }}</div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team A</th>
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team B</th>
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winner</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @foreach($matchesByCategory[$category->id][$roundName] as $match)
                                                                <tr>
                                                                    @php
                                                                        $full = function($user) {
                                                                            if (!$user) return null;
                                                                            if (!empty($user->full_name)) return $user->full_name;
                                                                            $first = trim($user->first_name ?? '');
                                                                            $last = trim($user->last_name ?? '');
                                                                            return trim($first . ' ' . $last) ?: null;
                                                                        };
                                                                        $teamA = array_filter([$full($match->player1), $full($match->player1Partner)]);
                                                                        $teamB = array_filter([$full($match->player2), $full($match->player2Partner)]);
                                                                    @endphp
                                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                                        {{ count($teamA) ? implode(' & ', $teamA) : 'TBD' }}
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                                        {{ count($teamB) ? implode(' & ', $teamB) : 'TBD' }}
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm text-gray-700">
                                                                        @if($match->result)
                                                                            @php
                                                                                $matchScoreService = app(\App\Services\MatchScoreService::class);
                                                                                $finalScore = $matchScoreService->calculateFinalScore($match->result);
                                                                                $setScores = $matchScoreService->getFormattedSetScores($match->result);
                                                                            @endphp
                                                                            <div class="space-y-1">
                                                                                <div class="font-bold text-gray-900">Final: {{ $finalScore['final_score'] }}</div>
                                                                                <div class="text-xs text-gray-600 space-y-0.5">
                                                                                    @foreach($setScores as $set => $score)
                                                                                        <div>{{ ucfirst(str_replace('set', 'Set ', $set)) }}: {{ $score }}</div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <span class="text-gray-400">TBD</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                                        @php
                                                                            $winnerNames = null;

                                                                            if ($match->status === 'completed' || $match->result) {
                                                                                if ($match->winner && $match->winnerPartner) {
                                                                                    $winnerNames = $full($match->winner) . ' & ' . $full($match->winnerPartner);
                                                                                } elseif ($match->winner && !$match->player1_partner_id && !$match->player2_partner_id) {
                                                                                    $winnerNames = $full($match->winner);
                                                                                } elseif ($match->winner_id) {
                                                                                    if ($match->player1_id === $match->winner_id || $match->player1_partner_id === $match->winner_id) {
                                                                                        $winnerNames = implode(' & ', $teamA);
                                                                                    } elseif ($match->player2_id === $match->winner_id || $match->player2_partner_id === $match->winner_id) {
                                                                                        $winnerNames = implode(' & ', $teamB);
                                                                                    }
                                                                                } elseif ($match->result) {
                                                                                    // Derive winner from scores if not explicitly set
                                                                                    $res = $match->result;
                                                                                    $team1Total = ($res->player1_set1_score ?? 0) + ($res->player1_set2_score ?? 0) + ($res->player1_set3_score ?? 0);
                                                                                    $team2Total = ($res->player2_set1_score ?? 0) + ($res->player2_set2_score ?? 0) + ($res->player2_set3_score ?? 0);
                                                                                    if ($team1Total !== $team2Total) {
                                                                                        $winnerNames = $team1Total > $team2Total ? implode(' & ', $teamA) : implode(' & ', $teamB);
                                                                                    }
                                                                                }
                                                                                if (!$winnerNames) {
                                                                                    $winnerNames = count($teamA) ? implode(' & ', $teamA) : (count($teamB) ? implode(' & ', $teamB) : 'Winner');
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        @if($winnerNames)
                                                                            <div class="text-sm font-bold text-green-600">
                                                                                {{ $winnerNames }}
                                                                            </div>
                                                                        @else
                                                                            <span class="text-gray-400">TBD</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-gray-600 text-sm border border-dashed border-gray-300 rounded-md p-4 bg-gray-50">
                                            @if($tournament->status === 'completed')
                                                No match records found for this category.
                                            @elseif($tournament->status === 'ongoing')
                                                Brackets are being updated. Upcoming matches will appear here; results show as soon as they are entered.
                                            @else
                                                Round 1 pairings will appear here once matches are generated.
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </main>

            <!-- Confirmation Dialog -->
            <div x-show="showConfirm" 
                 x-cloak
                 class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                 @click.self="closeConfirm()"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" 
                     @click.stop
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Action</h3>
                    <p class="text-gray-700 mb-4" x-text="confirmMessage"></p>
                    
                    <!-- Rejection Reason Input (only for reject-withdrawal type) -->
                    <div x-show="confirmType === 'reject-withdrawal'" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="rejectReason"
                            rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent"
                            placeholder="Please provide a reason for rejecting this withdrawal request..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">This message will be sent to the player.</p>
                    </div>
                    
                    <div class="flex gap-3 justify-end">
                        <button type="button" 
                                @click="closeConfirm()" 
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium transition">
                            Cancel
                        </button>
                        <button type="button" 
                                @click="executeConfirm()" 
                                :class="confirmType === 'reject-withdrawal' ? 'bg-red-600 hover:bg-red-700' : 'bg-[#2C5F4F] hover:bg-[#234b3f]'"
                                class="px-4 py-2 text-white rounded-md text-sm font-medium transition">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toast Notification -->
            <div x-show="showToast" 
                 x-cloak
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed top-4 right-4 z-50 max-w-md">
                <div :class="toastType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800'"
                     class="border rounded-lg shadow-lg p-4 flex items-center gap-3">
                    <div :class="toastType === 'error' ? 'text-red-600' : 'text-green-600'">
                        <svg x-show="toastType === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="toastType === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium flex-1" x-text="toastMessage"></p>
                    <button @click="showToast = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Reschedule Tournament Modal -->
            <div x-show="showRescheduleModal" 
                 x-cloak
                 class="fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4"
                 @click.self="closeRescheduleModal()"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" 
                     @click.stop
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Reschedule Tournament</h3>
                        <button @click="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form method="POST" action="{{ route('manager.tournaments.reschedule', $tournament) }}" id="reschedule-tournament-form">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Start Date <span class="text-red-500">*</span></label>
                                <input 
                                    type="date" 
                                    name="start_date" 
                                    x-model="rescheduleStartDate"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New End Date <span class="text-red-500">*</span></label>
                                <input 
                                    type="date" 
                                    name="end_date" 
                                    x-model="rescheduleEndDate"
                                    required
                                    :min="rescheduleStartDate"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                        </div>

                        <div class="flex gap-4 justify-end mt-6">
                            <button 
                                type="button"
                                @click="closeRescheduleModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium transition duration-200">
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-lg font-medium transition duration-200">
                                Reschedule Tournament
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
