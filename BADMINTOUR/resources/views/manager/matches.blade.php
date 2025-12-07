<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ 
    activeTab: 'overview',
    showRescheduleModal: false,
    showResultModal: false,
    showWalkoverModal: false,
    rescheduleMatchId: null,
    resultMatchId: null,
    walkoverMatchId: null,
    rescheduleDate: '',
    rescheduleTime: '',
    rescheduleCourt: 1,
    rescheduleMatch: null,
    resultMatch: null,
    walkoverMatch: null,
    player1Set1: '',
    player2Set1: '',
    player1Set2: '',
    player2Set2: '',
    player1Set3: '',
    player2Set3: '',
    selectedWinner: null,
    selectedWalkoverWinner: null,
    isLateInput: false,
    lateWarning: '',
    scheduleSortOrder: 'asc', // 'asc' or 'desc'
    ongoingSortOrder: 'asc',
    completedSortOrder: 'asc',
    toggleScheduleSort() {
        this.scheduleSortOrder = this.scheduleSortOrder === 'asc' ? 'desc' : 'asc';
        this.$nextTick(() => {
            this.sortTableRows('schedule-table', this.scheduleSortOrder);
        });
    },
    toggleOngoingSort() {
        this.ongoingSortOrder = this.ongoingSortOrder === 'asc' ? 'desc' : 'asc';
        this.$nextTick(() => {
            this.sortTableRows('ongoing-table', this.ongoingSortOrder);
        });
    },
    toggleCompletedSort() {
        this.completedSortOrder = this.completedSortOrder === 'asc' ? 'desc' : 'asc';
        this.$nextTick(() => {
            this.sortTableRows('completed-table', this.completedSortOrder);
        });
    },
    sortTableRows(tableId, sortOrder) {
        try {
            const table = document.getElementById(tableId);
            if (!table) return;
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            if (rows.length === 0) return;
            
            rows.sort((a, b) => {
                // Try to use data-sort-date attribute first (more reliable)
                const getDateValue = (row) => {
                    try {
                        // Check for data-sort-date attribute first
                        const sortDate = row.getAttribute('data-sort-date');
                        if (sortDate && sortDate !== '9999-12-31T23:59:59') {
                            return sortDate;
                        }
                        
                        // Fallback to parsing from text content
                        const dateCell = row.querySelector('td:first-child');
                        if (!dateCell) {
                            return sortOrder === 'asc' ? '9999-12-31T23:59:59' : '0000-01-01T00:00:00';
                        }
                        
                        const text = dateCell.textContent || dateCell.innerText || '';
                        const trimmedText = text.trim();
                        
                        if (!trimmedText || trimmedText === 'Not scheduled') {
                            return sortOrder === 'asc' ? '9999-12-31T23:59:59' : '0000-01-01T00:00:00';
                        }
                        
                        // Parse date and time from text
                        const parts = trimmedText.split(/\n|\s{2,}/);
                        const dateStr = parts[0] ? parts[0].trim() : '';
                        const timeStr = parts[1] ? parts[1].trim() : '';
                        
                        if (!dateStr) {
                            return sortOrder === 'asc' ? '9999-12-31T23:59:59' : '0000-01-01T00:00:00';
                        }
                        
                        // Try to parse the date
                        let date = new Date(dateStr);
                        if (isNaN(date.getTime())) {
                            return sortOrder === 'asc' ? '9999-12-31T23:59:59' : '0000-01-01T00:00:00';
                        }
                        
                        // Parse time if available
                        if (timeStr) {
                            const timeMatch = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
                            if (timeMatch) {
                                let hours = parseInt(timeMatch[1]);
                                const minutes = parseInt(timeMatch[2]);
                                const period = timeMatch[3].toUpperCase();
                                
                                if (period === 'PM' && hours !== 12) {
                                    hours += 12;
                                } else if (period === 'AM' && hours === 12) {
                                    hours = 0;
                                }
                                
                                date.setHours(hours, minutes, 0, 0);
                            }
                        }
                        
                        return date.toISOString();
                    } catch (e) {
                        console.error('Error parsing date:', e);
                        return sortOrder === 'asc' ? '9999-12-31T23:59:59' : '0000-01-01T00:00:00';
                    }
                };
                
                const valA = getDateValue(a);
                const valB = getDateValue(b);
                
                if (sortOrder === 'asc') {
                    return valA.localeCompare(valB);
                } else {
                    return valB.localeCompare(valA);
                }
            });
            
            // Clear and re-append sorted rows
            rows.forEach(row => {
                tbody.appendChild(row);
            });
        } catch (error) {
            console.error('Error sorting table rows:', error);
        }
    },
    openRescheduleModal(matchId, currentDate, currentTime, currentCourt) {
        this.rescheduleMatchId = matchId;
        this.rescheduleDate = currentDate || '';
        this.rescheduleTime = currentTime || '';
        this.rescheduleCourt = currentCourt || 1;
        this.showRescheduleModal = true;
    },
    closeRescheduleModal() {
        this.showRescheduleModal = false;
        this.rescheduleMatchId = null;
        this.rescheduleDate = '';
        this.rescheduleTime = '';
        this.rescheduleCourt = 1;
    },
    openResultModal(matchId, matchData) {
        this.resultMatchId = matchId;
        this.resultMatch = matchData;
        this.player1Set1 = '';
        this.player2Set1 = '';
        this.player1Set2 = '';
        this.player2Set2 = '';
        this.player1Set3 = '';
        this.player2Set3 = '';
        this.selectedWinner = null;
        this.isLateInput = matchData.isLateInput || false;
        this.lateWarning = matchData.lateWarning || '';
        this.showResultModal = true;
    },
    closeResultModal() {
        this.showResultModal = false;
        this.resultMatchId = null;
        this.resultMatch = null;
        this.player1Set1 = '';
        this.player2Set1 = '';
        this.player1Set2 = '';
        this.player2Set2 = '';
        this.player1Set3 = '';
        this.player2Set3 = '';
        this.selectedWinner = null;
        this.isLateInput = false;
        this.lateWarning = '';
    },
    openWalkoverModal(matchId, matchData) {
        this.walkoverMatchId = matchId;
        this.walkoverMatch = matchData;
        this.selectedWalkoverWinner = null;
        this.showWalkoverModal = true;
    },
    closeWalkoverModal() {
        this.showWalkoverModal = false;
        this.walkoverMatchId = null;
        this.walkoverMatch = null;
        this.selectedWalkoverWinner = null;
    }
}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-black">MATCH MANAGEMENT</h1>
                    <button class="text-gray-600 hover:text-gray-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if(!$club)
                    <!-- No Club State -->
                    <div class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No Club Yet</h3>
                        <p class="text-gray-500 mb-6 text-center max-w-md">Create your club first to start managing tournaments and matches.</p>
                        <a href="{{ route('manager.create-club') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Create Your Club
                        </a>
                    </div>
                @elseif($tournaments->count() === 0)
                    <!-- No Tournaments State -->
                    <div class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No Tournaments Available</h3>
                        <p class="text-gray-500 mb-6 text-center max-w-md">Create a tournament to generate matches and track player brackets.</p>
                        <a href="{{ route('manager.tournaments.create') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Create Tournament
                        </a>
                    </div>
                @else
                    <!-- Tournament Selector -->
                    <div class="mb-6 bg-white rounded-lg shadow p-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Tournament</label>
                        <select 
                            onchange="window.location.href='{{ route('manager.matches') }}?tournament=' + this.value"
                            class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            @foreach($allTournaments ?? $tournaments as $tournament)
                                <option value="{{ $tournament->id }}" {{ $selectedTournament && $selectedTournament->id === $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->name }} 
                                    @if($tournament->is_dual_meet)
                                        <span class="text-blue-600">[Dual Meet]</span>
                                    @endif
                                    ({{ ucfirst($tournament->status) }})
                                    @if($tournament->start_date)
                                        - {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedTournament)
                    <!-- Tournament Info Banner -->
                    @if($selectedTournament->is_dual_meet)
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900">Dual Meet Tournament</h3>
                                    <p class="text-sm text-blue-700">
                                        @if(isset($participatingClubs) && $participatingClubs->count() > 0)
                                            Participating Clubs: 
                                            @foreach($participatingClubs as $index => $participatingClub)
                                                {{ $participatingClub->name }}@if(!$loop->last), @endif
                                            @endforeach
                                        @else
                                            Interclub competition between multiple clubs
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tabs -->
                    <div class="border-b border-gray-300 mb-6">
                        <div class="flex space-x-8">
                            <button 
                                @click="activeTab = 'overview'" 
                                :class="activeTab === 'overview' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                                class="pb-3 px-2 uppercase text-sm transition duration-200">
                                Overview
                            </button>
                            <button 
                                @click="activeTab = 'ongoing'" 
                                :class="activeTab === 'ongoing' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                                class="pb-3 px-2 uppercase text-sm transition duration-200">
                                Ongoing
                            </button>
                            <button 
                                @click="activeTab = 'brackets'" 
                                :class="activeTab === 'brackets' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                                class="pb-3 px-2 uppercase text-sm transition duration-200">
                                Brackets
                            </button>
                            <button 
                                @click="activeTab = 'schedule'" 
                                :class="activeTab === 'schedule' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                                class="pb-3 px-2 uppercase text-sm transition duration-200">
                                Schedule
                            </button>
                            <button 
                                @click="activeTab = 'results'" 
                                :class="activeTab === 'results' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'"
                                class="pb-3 px-2 uppercase text-sm transition duration-200">
                                Results
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content with Data -->
                    <div x-show="activeTab === 'overview'" class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-bold">Match Overview - {{ $selectedTournament->name }}</h2>
                                @if($selectedTournament->is_dual_meet)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full uppercase">
                                        Dual Meet
                                    </span>
                                @endif
                            </div>
                            @php
                                // Use matchesByStatus from controller (already grouped and status-updated)
                                $scheduledCount = isset($matchesByStatus['scheduled']) ? $matchesByStatus['scheduled']->count() : 0;
                                $ongoingCount = isset($matchesByStatus['ongoing']) ? $matchesByStatus['ongoing']->count() : 0;
                                $completedCount = isset($matchesByStatus['completed']) ? $matchesByStatus['completed']->count() : 0;
                                $totalMatches = $scheduledCount + $ongoingCount + $completedCount;
                            @endphp
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <div class="text-sm text-gray-600">Scheduled</div>
                                    <div class="text-2xl font-bold text-blue-600">{{ $scheduledCount }}</div>
                                </div>
                                <div class="p-4 bg-yellow-50 rounded-lg">
                                    <div class="text-sm text-gray-600">Ongoing</div>
                                    <div class="text-2xl font-bold text-yellow-600">{{ $ongoingCount }}</div>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <div class="text-sm text-gray-600">Completed</div>
                                    <div class="text-2xl font-bold text-green-600">{{ $completedCount }}</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <p class="text-gray-600"><strong>Total Matches:</strong> {{ $totalMatches }}</p>
                                <p class="text-gray-600"><strong>Tournament Status:</strong> {{ ucfirst($selectedTournament->status) }}</p>
                                @if($selectedTournament->start_date)
                                    <p class="text-gray-600"><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($selectedTournament->start_date)->format('M d, Y') }}</p>
                                @endif
                            </div>
                            @if($totalMatches === 0)
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    @if($approvedRegistrations === 0)
                                        <p class="text-yellow-800 font-semibold mb-2">No matches can be generated yet because there are no registered players for this tournament.</p>
                                        <p class="text-yellow-700 text-sm mb-2">Players need to register and be approved before matches can be generated.</p>
                                        <a href="{{ route('manager.tournaments.show', $selectedTournament->id) }}" class="text-blue-600 hover:underline mt-2 inline-block">
                                            Go to tournament page to view registrations →
                                        </a>
                                    @else
                                        <p class="text-yellow-800">No matches have been generated for this tournament yet.</p>
                                        <p class="text-yellow-700 text-sm mb-2">This tournament has {{ $approvedRegistrations }} approved {{ $approvedRegistrations === 1 ? 'registration' : 'registrations' }}. Generate matches to create the tournament bracket.</p>
                                        <a href="{{ route('manager.tournaments.show', $selectedTournament->id) }}" class="text-blue-600 hover:underline mt-2 inline-block">
                                            Go to tournament page to generate matches →
                                        </a>
                                    @endif
                                </div>
                            @endif
                    </div>

                    <div x-show="activeTab === 'ongoing'" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold mb-4">Ongoing Matches - {{ $selectedTournament->name }}</h2>
                        @php
                                // Use matchesByStatus from controller (already filtered and status-updated)
                                $ongoingMatches = isset($matchesByStatus['ongoing']) ? $matchesByStatus['ongoing'] : collect([]);
                                
                                // Sort ongoing matches by date and time (earliest first)
                                $ongoingMatches = $ongoingMatches->sortBy(function($match) {
                                    if (!$match->scheduled_date || !$match->scheduled_time) {
                                        return '9999-12-31 23:59:59';
                                    }
                                    try {
                                        // Parse scheduled datetime properly
                                        $scheduledTime = $match->scheduled_time instanceof \Carbon\Carbon 
                                            ? $match->scheduled_time 
                                            : \Carbon\Carbon::parse($match->scheduled_time);
                                        
                                        $scheduledDate = $match->scheduled_date instanceof \Carbon\Carbon 
                                            ? $match->scheduled_date 
                                            : \Carbon\Carbon::parse($match->scheduled_date);
                                        
                                        $date = $scheduledDate->format('Y-m-d');
                                        $time = $scheduledTime->format('H:i:s');
                                        return $date . ' ' . $time;
                                    } catch (\Exception $e) {
                                        return '9999-12-31 23:59:59';
                                    }
                                })->values(); // Reset keys after sorting
                        @endphp
                        @if($ongoingMatches->count() > 0)
                                <div class="overflow-x-auto">
                                    <table id="ongoing-table" class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Players</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Round</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Court</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($ongoingMatches as $match)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $match->category->name ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        @php
                                                            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                                                            $categoryType = strtolower($match->category->name ?? '');
                                                            $isDoublesCategory = str_contains($categoryType, 'doubles') || str_contains($categoryType, 'mixed');
                                                        @endphp
                                                        @if($isDoublesCategory && $match->player1_partner_id)
                                                            <span class="font-medium">{{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}</span>
                                                            <span class="text-gray-500"> & </span>
                                                            <span class="font-medium">{{ optional($match->player1Partner)->first_name ?? 'TBD' }} {{ optional($match->player1Partner)->last_name ?? '' }}</span>
                                                        @else
                                                            <span class="font-medium">{{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}</span>
                                                        @endif
                                                        <span class="text-gray-500 mx-2"> vs </span>
                                                        @if($isDoublesCategory && $match->player2_partner_id)
                                                            <span class="font-medium">{{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}</span>
                                                            <span class="text-gray-500"> & </span>
                                                            <span class="font-medium">{{ optional($match->player2Partner)->first_name ?? 'TBD' }} {{ optional($match->player2Partner)->last_name ?? '' }}</span>
                                                        @else
                                                            <span class="font-medium">{{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $match->round ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Court {{ $match->court_number ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        @php
                                                            $matchData = [
                                                                'player1' => optional($match->player1)->first_name . ' ' . optional($match->player1)->last_name,
                                                                'player2' => optional($match->player2)->first_name . ' ' . optional($match->player2)->last_name,
                                                                'player1Id' => $match->player1_id,
                                                                'player2Id' => $match->player2_id,
                                                                'player1Partner' => $match->player1_partner_id ? (optional($match->player1Partner)->first_name . ' ' . optional($match->player1Partner)->last_name) : null,
                                                                'player2Partner' => $match->player2_partner_id ? (optional($match->player2Partner)->first_name . ' ' . optional($match->player2Partner)->last_name) : null,
                                                                'team1Name' => optional($match->player1)->first_name . ' ' . optional($match->player1)->last_name . ($match->player1_partner_id && optional($match->player1Partner) ? ' & ' . optional($match->player1Partner)->first_name . ' ' . optional($match->player1Partner)->last_name : ''),
                                                                'team2Name' => optional($match->player2)->first_name . ' ' . optional($match->player2)->last_name . ($match->player2_partner_id && optional($match->player2Partner) ? ' & ' . optional($match->player2Partner)->first_name . ' ' . optional($match->player2Partner)->last_name : ''),
                                                                'isLateInput' => false,
                                                                'lateWarning' => '',
                                                                'needsResult' => false,
                                                            ];
                                                        @endphp
                                                        
                                                        @if($isOwner && $match->player1_id && $match->player2_id)
                                                            <div class="flex flex-col gap-2">
                                                                <button 
                                                                    @click="openResultModal({{ $match->id }}, @js($matchData))"
                                                                    class="px-3 py-1.5 bg-[#2C5F4F] hover:bg-[#244D3E] text-white text-xs font-medium rounded transition duration-200">
                                                                    Input Result
                                                                </button>
                                                                <button 
                                                                    @click="openWalkoverModal({{ $match->id }}, @js($matchData))"
                                                                    class="px-3 py-1.5 bg-[#C85A54] hover:bg-[#B54A44] text-white text-xs font-medium rounded transition duration-200">
                                                                    Mark Walkover
                                                                </button>
                                                            </div>
                                                        @elseif(!$isOwner)
                                                            <span class="text-gray-400 text-xs italic">View only</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                        @else
                            <p class="text-gray-500">No ongoing matches at this time.</p>
                        @endif
                    </div>

                    <div x-show="activeTab === 'brackets'" class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-bold">Tournament Brackets - {{ $selectedTournament->name }}</h2>
                                @if($selectedTournament->is_dual_meet)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full uppercase">
                                        Dual Meet
                                    </span>
                                @endif
                            </div>
                            
                            @php
                                // Get matches for THIS tournament - use matchesByStatus to get all matches
                                $allMatches = collect([]);
                                if (isset($matchesByStatus)) {
                                    $allMatches = $matchesByStatus['scheduled']
                                        ->merge($matchesByStatus['ongoing'])
                                        ->merge($matchesByStatus['completed']);
                                }
                                // Fallback to tournament matches relationship if matchesByStatus is empty
                                if ($allMatches->isEmpty() && isset($selectedTournament->matches)) {
                                    $allMatches = $selectedTournament->matches;
                                }
                                $matches = $allMatches;
                            @endphp
                            
                            @if($matches->count() > 0 || (isset($selectedTournament) && $selectedTournament->matches && $selectedTournament->matches->count() > 0))
                                @php
                                    // If $matches is empty but tournament has matches, use tournament matches
                                    if ($matches->isEmpty() && isset($selectedTournament->matches)) {
                                        $matches = $selectedTournament->matches;
                                    }
                                @endphp
                                @php
                                    // Group matches by category first, then by round
                                    // Only include matches that have a valid category
                                    $matchesByCategory = $matches->filter(function($match) use ($categories) {
                                        return $match->tournament_category_id && $categories->contains('id', $match->tournament_category_id);
                                    })->groupBy('tournament_category_id');
                                @endphp
                                
                                @if($matchesByCategory->count() > 0)
                                    @foreach($matchesByCategory as $categoryId => $categoryMatches)
                                        @php
                                            $category = $categories->firstWhere('id', $categoryId);
                                            if (!$category) {
                                                continue; // Skip if category not found
                                            }
                                            
                                            // Validate that all matches in this category actually belong to it
                                            // For doubles/mixed doubles, don't filter by gender (teams can have mixed genders)
                                            $categoryType = strtolower($category->name ?? '');
                                            $isMenSingles = str_contains($categoryType, "men's singles") || str_contains($categoryType, 'mens singles') || $category->type === 'MS';
                                            $isWomenSingles = str_contains($categoryType, "women's singles") || str_contains($categoryType, 'womens singles') || $category->type === 'WS';
                                            $isDoublesCategory = str_contains($categoryType, 'doubles') || str_contains($categoryType, 'mixed') || in_array($category->type, ['MD', 'WD', 'XD']);
                                            
                                            $validMatches = $categoryMatches->filter(function($match) use ($categoryId, $isMenSingles, $isWomenSingles, $isDoublesCategory) {
                                                // Ensure match belongs to this category
                                                if ($match->tournament_category_id != $categoryId) {
                                                    return false;
                                                }
                                                
                                                // For doubles/mixed doubles, skip gender validation (teams can have mixed genders)
                                                if ($isDoublesCategory) {
                                                    return true; // All doubles matches are valid
                                                }
                                                
                                                // For singles categories, validate player gender matches category
                                                if ($isMenSingles) {
                                                    if ($match->player1 && $match->player1->gender !== 'male') {
                                                        return false;
                                                    }
                                                    if ($match->player2 && $match->player2->gender !== 'male') {
                                                        return false;
                                                    }
                                                }
                                                
                                                if ($isWomenSingles) {
                                                    if ($match->player1 && $match->player1->gender !== 'female') {
                                                        return false;
                                                    }
                                                    if ($match->player2 && $match->player2->gender !== 'female') {
                                                        return false;
                                                    }
                                                }
                                                
                                                return true;
                                            });
                                            
                                            if ($validMatches->count() === 0) {
                                                continue; // Skip if no valid matches
                                            }
                                            
                                            // Helper function to get round order for sorting
                                            $getRoundOrder = function($roundName) {
                                                // Extract round number or determine order
                                                if (preg_match('/Round 1/', $roundName)) {
                                                    return 1; // Round 1 is first
                                                } elseif (preg_match('/Quarterfinals/', $roundName)) {
                                                    return 2; // Quarterfinals is second
                                                } elseif (preg_match('/Semifinals/', $roundName)) {
                                                    return 3; // Semifinals is third
                                                } elseif (preg_match('/Finals/', $roundName)) {
                                                    return 4; // Finals is last
                                                } elseif (preg_match('/Round (\d+)/', $roundName, $matches)) {
                                                    return (int)$matches[1]; // Use round number
                                                }
                                                return 999; // Unknown rounds go last
                                            };
                                            
                                            // Group by round and sort by logical order
                                            $matchesByRound = $validMatches->groupBy('round');
                                            
                                            // Sort rounds by logical progression (Round 1 → Quarterfinals → Semifinals → Finals)
                                            $sortedRounds = $matchesByRound->sortBy(function($matches, $roundName) use ($getRoundOrder) {
                                                return $getRoundOrder($roundName);
                                            });
                                            
                                            $matchesByRound = $sortedRounds;
                                            $maxRound = $matchesByRound->keys()->max() ?? 1;
                                        @endphp
                                        
                                        <div class="mb-8">
                                            <h3 class="text-lg font-semibold text-black mb-4 pb-2 border-b-2 border-[#2C5F4F]">
                                                {{ $category->name ?? 'Unknown Category' }}
                                                <span class="text-sm text-gray-500 font-normal ml-2">
                                                    ({{ $validMatches->count() }} {{ $validMatches->count() === 1 ? 'match' : 'matches' }})
                                                </span>
                                            </h3>
                                            
                                            <div class="overflow-x-auto">
                                                <div class="flex gap-6 min-w-max pb-4">
                                                    @foreach($matchesByRound as $round => $roundMatches)
                                                        <div class="flex-shrink-0 w-72">
                                                            <h4 class="text-center font-semibold text-gray-700 mb-4 pb-2 border-b-2 border-[#D4A574] bg-gray-50 py-2 rounded-t-lg">
                                                                {{ $round }}
                                                            </h4>
                                                            <div class="space-y-3">
                                                                @foreach($roundMatches->sortBy('match_number') as $match)
                                                                    <div class="border-2 rounded-lg overflow-hidden {{ $match->status === 'completed' ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white' }}">
                                                                        <div class="p-3 {{ ($match->result && $match->result->winner_id === $match->player1_id) ? 'bg-green-100' : 'bg-gray-50' }} border-b">
                                                                            <div class="flex justify-between items-center">
                                                                                <div class="font-medium text-sm flex-1 min-w-0">
                                                                                    @php
                                                                                        $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                                                                                        $categoryType = strtolower($match->category->name ?? '');
                                                                                        $isDoublesCategory = str_contains($categoryType, 'doubles') || str_contains($categoryType, 'mixed');
                                                                                    @endphp
                                                                                    @if($isDoublesCategory && $match->player1_partner_id && optional($match->player1Partner))
                                                                                        <div class="truncate">
                                                                                            <span>{{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}</span>
                                                                                            <span class="text-gray-500"> & </span>
                                                                                            <span>{{ optional($match->player1Partner)->first_name }} {{ optional($match->player1Partner)->last_name }}</span>
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="truncate">
                                                                                            {{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                @if($match->result)
                                                                                    <span class="font-bold text-sm ml-2 whitespace-nowrap">
                                                                                        {{ $match->result->player1_set1_score ?? '-' }}
                                                                                        @if($match->result->player1_set2_score !== null), {{ $match->result->player1_set2_score }}@endif
                                                                                        @if($match->result->player1_set3_score !== null), {{ $match->result->player1_set3_score }}@endif
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="p-3 {{ ($match->result && $match->result->winner_id === $match->player2_id) ? 'bg-green-100' : 'bg-white' }}">
                                                                            <div class="flex justify-between items-center">
                                                                                <div class="font-medium text-sm flex-1 min-w-0">
                                                                                    @if($isDoublesCategory && $match->player2_partner_id && optional($match->player2Partner))
                                                                                        <div class="truncate">
                                                                                            <span>{{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}</span>
                                                                                            <span class="text-gray-500"> & </span>
                                                                                            <span>{{ optional($match->player2Partner)->first_name }} {{ optional($match->player2Partner)->last_name }}</span>
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="truncate">
                                                                                            {{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                @if($match->result)
                                                                                    <span class="font-bold text-sm ml-2 whitespace-nowrap">
                                                                                        {{ $match->result->player2_set1_score ?? '-' }}
                                                                                        @if($match->result->player2_set2_score !== null), {{ $match->result->player2_set2_score }}@endif
                                                                                        @if($match->result->player2_set3_score !== null), {{ $match->result->player2_set3_score }}@endif
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        @if($match->scheduled_date || $match->court_number)
                                                                            <div class="px-3 py-2 bg-gray-50 border-t text-xs text-gray-500">
                                                                                @if($match->scheduled_date)
                                                                                    {{ \Carbon\Carbon::parse($match->scheduled_date)->format('M d') }}
                                                                                    @if($match->scheduled_time)
                                                                                        {{ \Carbon\Carbon::parse($match->scheduled_time)->format('h:i A') }}
                                                                                    @endif
                                                                                @endif
                                                                                @if($match->court_number)
                                                                                    @if($match->scheduled_date) • @endif
                                                                                    Court {{ $match->court_number }}
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <p class="text-gray-500">No valid brackets found for this tournament</p>
                                        <p class="text-sm text-gray-400 mt-1">Matches may not be properly categorized</p>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-gray-500">No bracket generated yet</p>
                                    @if($selectedTournament->status === 'published' || $selectedTournament->status === 'upcoming')
                                        <p class="text-sm text-gray-400 mt-1">Generate matches first to create the bracket</p>
                                    @else
                                        <p class="text-sm text-gray-400 mt-1">Publish the tournament first, then generate matches</p>
                                    @endif
                                </div>
                            @endif
                    </div>

                    <div x-show="activeTab === 'schedule'" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold mb-4">Match Schedule - {{ $selectedTournament->name }}</h2>
                        @php
                                // Get ALL scheduled matches (including future ones with TBD players)
                                // Use matchesByStatus from controller, but also include matches from tournament relationship
                                $scheduledMatches = isset($matchesByStatus['scheduled']) ? $matchesByStatus['scheduled'] : collect([]);
                                
                                // Also get matches that are scheduled but might not be in matchesByStatus
                                if (isset($selectedTournament->matches)) {
                                    $allScheduled = $selectedTournament->matches->filter(function($m) {
                                        return $m->status === 'scheduled' && $m->scheduled_date && $m->scheduled_time;
                                    });
                                    $scheduledMatches = $scheduledMatches->merge($allScheduled)->unique('id');
                                }
                                
                                // Sort by date and time chronologically (earliest first)
                                $scheduledMatches = $scheduledMatches->sortBy(function($match) {
                                    if (!$match->scheduled_date || !$match->scheduled_time) {
                                        return '9999-12-31 23:59:59'; // Put unscheduled matches at the end
                                    }
                                    try {
                                        // Parse scheduled datetime properly (scheduled_time is cast as datetime in model)
                                        $scheduledTime = $match->scheduled_time instanceof \Carbon\Carbon 
                                            ? $match->scheduled_time 
                                            : \Carbon\Carbon::parse($match->scheduled_time);
                                        
                                        $scheduledDate = $match->scheduled_date instanceof \Carbon\Carbon 
                                            ? $match->scheduled_date 
                                            : \Carbon\Carbon::parse($match->scheduled_date);
                                        
                                        $date = $scheduledDate->format('Y-m-d');
                                        $time = $scheduledTime->format('H:i:s');
                                        return $date . ' ' . $time;
                                    } catch (\Exception $e) {
                                        return '9999-12-31 23:59:59'; // Put parsing errors at the end
                                    }
                                })->values(); // Reset keys after sorting to preserve order
                                
                                // Count matches needing results (matches that have ended but no result entered)
                                $matchesNeedingResults = $scheduledMatches->filter(function($match) {
                                    if (!$match->scheduled_date || !$match->scheduled_time || $match->result || !$match->player1_id || !$match->player2_id) {
                                        return false;
                                    }
                                    try {
                                        // Parse scheduled datetime (scheduled_time is cast as datetime in model)
                                        $scheduledTime = $match->scheduled_time instanceof \Carbon\Carbon 
                                            ? $match->scheduled_time 
                                            : \Carbon\Carbon::parse($match->scheduled_time);
                                        
                                        $scheduledDate = $match->scheduled_date instanceof \Carbon\Carbon 
                                            ? $match->scheduled_date 
                                            : \Carbon\Carbon::parse($match->scheduled_date);
                                        
                                        // Combine date and time
                                        $matchDateTime = $scheduledDate->copy()
                                            ->setTime($scheduledTime->hour, $scheduledTime->minute, $scheduledTime->second);
                                        
                                        $category = $match->category;
                                        $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                                        $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
                                        
                                        // Only count if match has ended (past end time)
                                        return \Carbon\Carbon::now()->isAfter($matchEndTime);
                                    } catch (\Exception $e) {
                                        return false;
                                    }
                                });
                        @endphp
                        
                        @if($matchesNeedingResults->count() > 0 && $isOwner)
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-red-800 font-semibold">
                                        {{ $matchesNeedingResults->count() }} match(es) need results entered
                                    </p>
                                    <p class="text-red-700 text-sm mt-1">
                                        These matches have passed their scheduled time. Please input results to update brackets and rankings.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($scheduledMatches->count() > 0)
                                <div class="overflow-x-auto">
                                    <table id="schedule-table" class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                    <button @click="toggleScheduleSort()" class="flex items-center space-x-1 hover:text-gray-700 focus:outline-none group">
                                                        <span>Date & Time</span>
                                                        <span class="flex flex-col -space-y-1">
                                                            <svg class="w-3 h-3 transition-colors" :class="scheduleSortOrder === 'asc' ? 'text-[#2C5F4F]' : 'text-gray-300 group-hover:text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <svg class="w-3 h-3 transition-colors" :class="scheduleSortOrder === 'desc' ? 'text-[#2C5F4F]' : 'text-gray-300 group-hover:text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Players</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Round</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Court</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($scheduledMatches as $match)
                                                @php
                                                    // Check if match needs result (past scheduled time) - calculate early for use in date display
                                                    // Only highlight if match has ended but no result entered
                                                    $needsResult = false;
                                                    if ($match->scheduled_date && $match->scheduled_time && !$match->result && $match->player1_id && $match->player2_id) {
                                                        try {
                                                            // Parse scheduled datetime (scheduled_time is cast as datetime in model)
                                                            $scheduledTime = $match->scheduled_time instanceof \Carbon\Carbon 
                                                                ? $match->scheduled_time 
                                                                : \Carbon\Carbon::parse($match->scheduled_time);
                                                            
                                                            $scheduledDate = $match->scheduled_date instanceof \Carbon\Carbon 
                                                                ? $match->scheduled_date 
                                                                : \Carbon\Carbon::parse($match->scheduled_date);
                                                            
                                                            // Combine date and time
                                                            $matchDateTime = $scheduledDate->copy()
                                                                ->setTime($scheduledTime->hour, $scheduledTime->minute, $scheduledTime->second);
                                                            
                                                            $category = $match->category;
                                                            $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                                                            $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
                                                            
                                                            // Only mark as needing result if match has ended (past end time)
                                                            $now = \Carbon\Carbon::now();
                                                            if ($now->isAfter($matchEndTime)) {
                                                                $needsResult = true;
                                                            }
                                                        } catch (\Exception $e) {
                                                            // If parsing fails, don't highlight
                                                            $needsResult = false;
                                                        }
                                                    }
                                                @endphp
                                                <tr data-sort-date="{{ $match->scheduled_date && $match->scheduled_time ? \Carbon\Carbon::parse($match->scheduled_date)->setTimeFromTimeString(\Carbon\Carbon::parse($match->scheduled_time)->format('H:i:s'))->toISOString() : '9999-12-31T23:59:59' }}">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @if($match->scheduled_date)
                                                            <div class="{{ $needsResult ? 'text-red-600 font-semibold' : '' }}">
                                                                {{ \Carbon\Carbon::parse($match->scheduled_date)->format('M d, Y') }}
                                                                @if($match->scheduled_time)
                                                                    <br><span class="text-gray-500">{{ \Carbon\Carbon::parse($match->scheduled_time)->format('h:i A') }}</span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400">Not scheduled</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $match->category->name ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        {{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}
                                                        @if($match->player1_partner_id)
                                                            / {{ optional($match->player1Partner)->first_name ?? 'TBD' }} {{ optional($match->player1Partner)->last_name ?? '' }}
                                                        @endif
                                                        <span class="text-gray-500"> vs </span>
                                                        {{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}
                                                        @if($match->player2_partner_id)
                                                            / {{ optional($match->player2Partner)->first_name ?? 'TBD' }} {{ optional($match->player2Partner)->last_name ?? '' }}
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $match->round ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Court {{ $match->court_number ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        @php
                                                            $canReschedule = $match->status === 'scheduled' && ($match->reschedule_count ?? 0) < 1;
                                                            $currentDate = $match->scheduled_date ? \Carbon\Carbon::parse($match->scheduled_date)->format('Y-m-d') : '';
                                                            $currentTime = $match->scheduled_time ? \Carbon\Carbon::parse($match->scheduled_time)->format('H:i') : '';
                                                            $currentCourt = $match->court_number ?? 1;
                                                            
                                                            // Calculate late input details (needsResult already calculated above)
                                                            $isLateInput = false;
                                                            $lateWarning = '';
                                                            if ($needsResult) {
                                                                $isLateInput = true;
                                                                $matchDateTime = \Carbon\Carbon::parse($match->scheduled_date)->setTimeFromTimeString($match->scheduled_time);
                                                                $category = $match->category;
                                                                $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                                                                $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
                                                                $hoursLate = \Carbon\Carbon::now()->diffInHours($matchEndTime);
                                                                $lateWarning = "This match ended {$hoursLate} hour(s) ago. Result pending.";
                                                            }
                                                            
                                                            $matchData = [
                                                                'player1' => optional($match->player1)->first_name . ' ' . optional($match->player1)->last_name,
                                                                'player2' => optional($match->player2)->first_name . ' ' . optional($match->player2)->last_name,
                                                                'player1Id' => $match->player1_id,
                                                                'player2Id' => $match->player2_id,
                                                                'player1Partner' => $match->player1_partner_id ? (optional($match->player1Partner)->first_name . ' ' . optional($match->player1Partner)->last_name) : null,
                                                                'player2Partner' => $match->player2_partner_id ? (optional($match->player2Partner)->first_name . ' ' . optional($match->player2Partner)->last_name) : null,
                                                                'team1Name' => optional($match->player1)->first_name . ' ' . optional($match->player1)->last_name . ($match->player1_partner_id && optional($match->player1Partner) ? ' & ' . optional($match->player1Partner)->first_name . ' ' . optional($match->player1Partner)->last_name : ''),
                                                                'team2Name' => optional($match->player2)->first_name . ' ' . optional($match->player2)->last_name . ($match->player2_partner_id && optional($match->player2Partner) ? ' & ' . optional($match->player2Partner)->first_name . ' ' . optional($match->player2Partner)->last_name : ''),
                                                                'isLateInput' => $isLateInput,
                                                                'lateWarning' => $lateWarning,
                                                                'needsResult' => $needsResult,
                                                            ];
                                                        @endphp
                                                        
                                                        <div class="flex flex-col gap-2">
                                                            @if($isOwner && $match->player1_id && $match->player2_id)
                                                                @if($needsResult)
                                                                    <span class="text-xs text-red-600 font-medium mb-1">Result Pending</span>
                                                                @endif
                                                                <button 
                                                                    @click="openResultModal({{ $match->id }}, @js($matchData))"
                                                                    class="px-3 py-1.5 bg-[#2C5F4F] hover:bg-[#244D3E] text-white text-xs font-medium rounded transition duration-200">
                                                                    Input Result
                                                                </button>
                                                                <button 
                                                                    @click="openWalkoverModal({{ $match->id }}, @js($matchData))"
                                                                    class="px-3 py-1.5 bg-[#C85A54] hover:bg-[#B54A44] text-white text-xs font-medium rounded transition duration-200">
                                                                    Mark Walkover
                                                                </button>
                                                            @endif
                                                            
                                                            @if($isOwner && $canReschedule)
                                                                <button 
                                                                    @click="openRescheduleModal({{ $match->id }}, '{{ $currentDate }}', '{{ $currentTime }}', {{ $currentCourt }})"
                                                                    class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded transition duration-200">
                                                                    Reschedule
                                                                </button>
                                                            @elseif(($match->reschedule_count ?? 0) >= 1)
                                                                <span class="text-gray-400 text-xs italic">Already rescheduled</span>
                                                            @elseif(!$isOwner)
                                                                <span class="text-gray-400 text-xs italic">View only</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                        @else
                                <p class="text-gray-500">No scheduled matches. Matches will appear here once they are generated.</p>
                        @endif
                    </div>

                    <div x-show="activeTab === 'results'" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold mb-4">Match Results - {{ $selectedTournament->name }}</h2>
                        @php
                                // Use matchesByStatus from controller (already filtered and status-updated)
                                $completedMatches = isset($matchesByStatus['completed']) ? $matchesByStatus['completed'] : collect([]);
                                
                                // Sort completed matches by date and time (earliest first)
                                $completedMatches = $completedMatches->sortBy(function($match) {
                                    if (!$match->scheduled_date || !$match->scheduled_time) {
                                        return '9999-12-31 23:59:59';
                                    }
                                    try {
                                        // Parse scheduled datetime properly
                                        $scheduledTime = $match->scheduled_time instanceof \Carbon\Carbon 
                                            ? $match->scheduled_time 
                                            : \Carbon\Carbon::parse($match->scheduled_time);
                                        
                                        $scheduledDate = $match->scheduled_date instanceof \Carbon\Carbon 
                                            ? $match->scheduled_date 
                                            : \Carbon\Carbon::parse($match->scheduled_date);
                                        
                                        $date = $scheduledDate->format('Y-m-d');
                                        $time = $scheduledTime->format('H:i:s');
                                        return $date . ' ' . $time;
                                    } catch (\Exception $e) {
                                        return '9999-12-31 23:59:59';
                                    }
                                })->values(); // Reset keys after sorting
                        @endphp
                        @if($completedMatches->count() > 0)
                                <div class="overflow-x-auto">
                                    <table id="completed-table" class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Players</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Round</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Winner</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                                                @if($isOwner)
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($completedMatches as $match)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $match->category->name ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        @php
                                                            $categoryType = strtolower($match->category->name ?? '');
                                                            $isDoublesCategory = str_contains($categoryType, 'doubles') || str_contains($categoryType, 'mixed');
                                                        @endphp
                                                        @if($isDoublesCategory && $match->player1_partner_id)
                                                            <span class="font-medium">{{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}</span>
                                                            <span class="text-gray-500"> & </span>
                                                            <span class="font-medium">{{ optional($match->player1Partner)->first_name ?? 'TBD' }} {{ optional($match->player1Partner)->last_name ?? '' }}</span>
                                                        @else
                                                            <span class="font-medium">{{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}</span>
                                                        @endif
                                                        <span class="text-gray-500 mx-2"> vs </span>
                                                        @if($isDoublesCategory && $match->player2_partner_id)
                                                            <span class="font-medium">{{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}</span>
                                                            <span class="text-gray-500"> & </span>
                                                            <span class="font-medium">{{ optional($match->player2Partner)->first_name ?? 'TBD' }} {{ optional($match->player2Partner)->last_name ?? '' }}</span>
                                                        @else
                                                            <span class="font-medium">{{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $match->round ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @if($match->winner_id)
                                                            @if($isDoublesCategory && $match->winner_partner_id && optional($match->winnerPartner))
                                                                <span class="font-medium">{{ optional($match->winner)->first_name ?? 'N/A' }} {{ optional($match->winner)->last_name ?? '' }}</span>
                                                                <span class="text-gray-500"> & </span>
                                                                <span class="font-medium">{{ optional($match->winnerPartner)->first_name }} {{ optional($match->winnerPartner)->last_name }}</span>
                                                            @else
                                                                {{ optional($match->winner)->first_name ?? 'N/A' }} {{ optional($match->winner)->last_name ?? '' }}
                                                            @endif
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @if($match->result)
                                                            @php
                                                                $result = $match->result;
                                                                $set1 = ($result->player1_set1_score ?? 0) . '-' . ($result->player2_set1_score ?? 0);
                                                                $set2 = ($result->player1_set2_score ?? null) ? ($result->player1_set2_score . '-' . $result->player2_set2_score) : null;
                                                                $set3 = ($result->player1_set3_score ?? null) ? ($result->player1_set3_score . '-' . $result->player2_set3_score) : null;
                                                                $score = $set1 . ($set2 ? ' / ' . $set2 : '') . ($set3 ? ' / ' . $set3 : '');
                                                            @endphp
                                                            {{ $score }}
                                                        @else
                                                            <span class="text-red-600 font-medium">No score recorded</span>
                                                        @endif
                                                    </td>
                                                    @if($isOwner)
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        @if(!$match->result && $match->player1_id && $match->player2_id)
                                                            @php
                                                                $matchData = [
                                                                    'player1' => optional($match->player1)->first_name . ' ' . optional($match->player1)->last_name,
                                                                    'player2' => optional($match->player2)->first_name . ' ' . optional($match->player2)->last_name,
                                                                    'player1Id' => $match->player1_id,
                                                                    'player2Id' => $match->player2_id,
                                                                    'player1Partner' => $match->player1_partner_id ? (optional($match->player1Partner)->first_name . ' ' . optional($match->player1Partner)->last_name) : null,
                                                                    'player2Partner' => $match->player2_partner_id ? (optional($match->player2Partner)->first_name . ' ' . optional($match->player2Partner)->last_name) : null,
                                                                    'team1Name' => optional($match->player1)->first_name . ' ' . optional($match->player1)->last_name . ($match->player1_partner_id && optional($match->player1Partner) ? ' & ' . optional($match->player1Partner)->first_name . ' ' . optional($match->player1Partner)->last_name : ''),
                                                                    'team2Name' => optional($match->player2)->first_name . ' ' . optional($match->player2)->last_name . ($match->player2_partner_id && optional($match->player2Partner) ? ' & ' . optional($match->player2Partner)->first_name . ' ' . optional($match->player2Partner)->last_name : ''),
                                                                    'isLateInput' => true,
                                                                    'lateWarning' => 'This match is marked as completed but has no result. Please input the result.',
                                                                    'needsResult' => true,
                                                                ];
                                                            @endphp
                                                            <button 
                                                                @click="openResultModal({{ $match->id }}, @js($matchData))"
                                                                class="px-3 py-1.5 bg-[#2C5F4F] hover:bg-[#244D3E] text-white text-xs font-medium rounded transition duration-200">
                                                                Input Result
                                                            </button>
                                                        @endif
                                                    </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                        @else
                            <p class="text-gray-500">No completed matches yet.</p>
                        @endif
                    </div>
                    @else
                        <div class="bg-white rounded-lg shadow p-6">
                            <p class="text-gray-500">Please select a tournament from the dropdown above to view matches.</p>
                        </div>
                    @endif
                @endif
            </main>
        </div>
    </div>

    <!-- Reschedule Match Modal -->
    <div x-show="showRescheduleModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         @click.self="closeRescheduleModal()"
         x-transition>
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Reschedule Match</h3>
                <button @click="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Warning Message -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Important:</strong> You can only reschedule this match once. Players will be notified of the new schedule.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" :action="`{{ url('/manager/matches') }}/${rescheduleMatchId}/reschedule`" @submit.prevent="
                if (confirm('You can only reschedule this match once. Do you want to proceed?')) {
                    $event.target.action = `{{ url('/manager/matches') }}/${rescheduleMatchId}/reschedule`;
                    $event.target.submit();
                }
            ">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Date <span class="text-red-500">*</span></label>
                        <input 
                            type="date" 
                            name="scheduled_date" 
                            x-model="rescheduleDate"
                            required
                            @if($selectedTournament)
                                min="{{ $selectedTournament->start_date ? \Carbon\Carbon::parse($selectedTournament->start_date)->format('Y-m-d') : '' }}"
                                max="{{ $selectedTournament->end_date ? \Carbon\Carbon::parse($selectedTournament->end_date)->format('Y-m-d') : '' }}"
                            @endif
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Time <span class="text-red-500">*</span></label>
                        <input 
                            type="time" 
                            name="scheduled_time" 
                            x-model="rescheduleTime"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Court Number <span class="text-red-500">*</span></label>
                        <select 
                            name="court_number" 
                            x-model="rescheduleCourt"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            @if($selectedTournament)
                                @for($i = 1; $i <= ($selectedTournament->number_of_courts ?? 1); $i++)
                                    <option value="{{ $i }}">Court {{ $i }}</option>
                                @endfor
                            @else
                                <option value="1">Court 1</option>
                            @endif
                        </select>
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
                        Confirm Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Input Result Modal -->
    <div x-show="showResultModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         @click.self="closeResultModal()"
         x-transition>
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Input Match Result</h3>
                <button @click="closeResultModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Late Input Warning -->
            <div x-show="isLateInput" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-yellow-700" x-text="lateWarning || 'This result is being entered after the scheduled match time.'"></p>
                </div>
            </div>

            <!-- Match Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2" x-show="resultMatch">
                    <span class="font-semibold" x-text="resultMatch?.player1"></span>
                    <span x-show="resultMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player1Partner"></span></span>
                    <span class="text-gray-500 mx-2"> vs </span>
                    <span class="font-semibold" x-text="resultMatch?.player2"></span>
                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                </p>
            </div>

            <form method="POST" :action="`{{ url('/manager/matches') }}/${resultMatchId}/record-result`">
                @csrf
                
                <!-- Set Scores -->
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set 1 Scores <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player1"></span>
                                    <span x-show="resultMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player1Partner"></span></span>
                                </label>
                                <input type="number" name="player1_set1_score" x-model="player1Set1" min="0" max="30" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player2"></span>
                                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                                </label>
                                <input type="number" name="player2_set1_score" x-model="player2Set1" min="0" max="30" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set 2 Scores (Optional)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player1"></span>
                                    <span x-show="resultMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player1Partner"></span></span>
                                </label>
                                <input type="number" name="player1_set2_score" x-model="player1Set2" min="0" max="30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player2"></span>
                                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                                </label>
                                <input type="number" name="player2_set2_score" x-model="player2Set2" min="0" max="30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set 3 Scores (Optional)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player1"></span>
                                    <span x-show="resultMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player1Partner"></span></span>
                                </label>
                                <input type="number" name="player1_set3_score" x-model="player1Set3" min="0" max="30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player2"></span>
                                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                                </label>
                                <input type="number" name="player2_set3_score" x-model="player2Set3" min="0" max="30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Winner Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Winner <span class="text-red-500">*</span></label>
                    <select name="winner_id" x-model="selectedWinner" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                        <option value="">Select Winner</option>
                        <option :value="resultMatch?.player1Id" x-text="resultMatch?.team1Name || resultMatch?.player1"></option>
                        <option :value="resultMatch?.player2Id" x-text="resultMatch?.team2Name || resultMatch?.player2"></option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeResultModal()" 
                            class="px-4 py-2 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!selectedWinner || !player1Set1 || !player2Set1"
                            :class="(!selectedWinner || !player1Set1 || !player2Set1) ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#2C5F4F] hover:bg-[#244D3E]'"
                            class="px-4 py-2 text-white font-semibold rounded-lg transition">
                        Save Result
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Walkover Modal -->
    <div x-show="showWalkoverModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         @click.self="closeWalkoverModal()"
         x-transition>
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Mark as Walkover</h3>
                <button @click="closeWalkoverModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <p class="text-sm text-yellow-700">
                    <strong>Note:</strong> Walkovers do not affect ELO ratings. The selected winner will advance to the next round.
                </p>
            </div>

            <!-- Match Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2" x-show="walkoverMatch">
                    <span class="font-semibold" x-text="walkoverMatch?.player1"></span>
                    <span x-show="walkoverMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="walkoverMatch?.player1Partner"></span></span>
                    <span class="text-gray-500 mx-2"> vs </span>
                    <span class="font-semibold" x-text="walkoverMatch?.player2"></span>
                    <span x-show="walkoverMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="walkoverMatch?.player2Partner"></span></span>
                </p>
            </div>

            <form method="POST" :action="`{{ url('/manager/matches') }}/${walkoverMatchId}/walkover`">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Winner (Walkover) <span class="text-red-500">*</span></label>
                    <select name="winner_id" x-model="selectedWalkoverWinner" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C85A54]">
                        <option value="">Select Winner</option>
                        <option :value="walkoverMatch?.player1Id" x-text="walkoverMatch?.team1Name || walkoverMatch?.player1"></option>
                        <option :value="walkoverMatch?.player2Id" x-text="walkoverMatch?.team2Name || walkoverMatch?.player2"></option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeWalkoverModal()" 
                            class="px-4 py-2 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!selectedWalkoverWinner"
                            :class="!selectedWalkoverWinner ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#C85A54] hover:bg-[#B54A44]'"
                            class="px-4 py-2 text-white font-semibold rounded-lg transition">
                        Mark Walkover
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
