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
    showRescheduleConfirm: false,
    showResultModal: false,
    showWalkoverModal: false,
    rescheduleMatchId: null,
    resultMatchId: null,
    walkoverMatchId: null,
    walkoverMatch: null,
    walkoverWinnerConfirmed: false,
    rescheduleDate: '',
    rescheduleTime: '',
    rescheduleCourt: 1,
    rescheduleMatch: null,
    resultMatch: null,
    player1Set1: '',
    player2Set1: '',
    player1Set2: '',
    player2Set2: '',
    player1Set3: '',
    player2Set3: '',
    selectedWinner: null,
    autoDeterminedWinner: null,
    autoDeterminedWinnerName: '',
    selectedWalkoverWinner: null,
    isLateInput: false,
    lateWarning: '',
    set3Required: false,
    set3Enabled: false,
    set1Error: '',
    set2Error: '',
    set3Error: '',
    validateBadmintonScore(p1Score, p2Score) {
        const p1 = parseInt(p1Score) || 0;
        const p2 = parseInt(p2Score) || 0;
        
        if (p1 === 0 && p2 === 0) return { valid: true, error: '' };
        
        // Check maximum score
        if (p1 > 30 || p2 > 30) {
            return { valid: false, error: 'Maximum score per set is 30 points.' };
        }
        
        // Special case: Allow 21-0 or 0-21 for walkover scenarios (absent player)
        // Manager can input 21-0 for present player when opponent is absent
        if ((p1 === 21 && p2 === 0) || (p1 === 0 && p2 === 21)) {
            return { valid: true, error: '' };
        }
        
        // Check if scores are equal
        if (p1 === p2) {
            return { valid: false, error: 'A set cannot end in a tie. One player must win by at least 2 points.' };
        }
        
        const higher = Math.max(p1, p2);
        const lower = Math.min(p1, p2);
        const diff = higher - lower;
        
        // Check 29-29 tiebreaker rule
        if (p1 === 29 && p2 === 29) {
            return { valid: false, error: 'If score is 29-29, the set must continue until one player reaches 30.' };
        }
        
        // If one player reaches 30, they must have won from 29-29
        if (higher === 30) {
            if (lower !== 29) {
                return { valid: false, error: 'A score of 30 can only occur when the set was tied at 29-29.' };
            }
            return { valid: true, error: '' };
        }
        
        // If neither reached 30, check if someone reached 21
        if (higher < 21) {
            return { valid: false, error: 'A set must be won by reaching at least 21 points.' };
        }
        
        // If someone reached 21 or more, they must win by at least 2 points
        if (higher >= 21 && diff < 2) {
            return { valid: false, error: 'A set must be won by at least 2 points (e.g., 21-19, 22-20, 23-21).' };
        }
        
        return { valid: true, error: '' };
    },
    checkSet3Requirement() {
        const p1s1 = parseInt(this.player1Set1) || 0;
        const p2s1 = parseInt(this.player2Set1) || 0;
        const p1s2 = parseInt(this.player1Set2) || 0;
        const p2s2 = parseInt(this.player2Set2) || 0;
        
        const set1Validation = this.validateBadmintonScore(p1s1, p2s1);
        this.set1Error = set1Validation.error || '';
        
        const set2Validation = this.validateBadmintonScore(p1s2, p2s2);
        this.set2Error = set2Validation.error || '';
        
        if (p1s1 > 0 && p2s1 > 0 && p1s2 > 0 && p2s2 > 0 && set1Validation.valid && set2Validation.valid) {
            const set1Winner = p1s1 > p2s1 ? 'player1' : 'player2';
            const set2Winner = p1s2 > p2s2 ? 'player1' : 'player2';
            this.set3Required = set1Winner !== set2Winner;
            this.set3Enabled = this.set3Required;
            
            if (!this.set3Required) {
                this.player1Set3 = '';
                this.player2Set3 = '';
                this.set3Error = '';
                this.determineWinner();
            } else {
                this.autoDeterminedWinner = null;
                this.autoDeterminedWinnerName = '';
            }
        } else {
            this.set3Required = false;
            this.set3Enabled = false;
            this.autoDeterminedWinner = null;
            this.autoDeterminedWinnerName = '';
        }
        
        if (this.set3Required && this.set3Enabled) {
            const p1s3 = parseInt(this.player1Set3) || 0;
            const p2s3 = parseInt(this.player2Set3) || 0;
            if (p1s3 > 0 || p2s3 > 0) {
                const set3Validation = this.validateBadmintonScore(p1s3, p2s3);
                this.set3Error = set3Validation.error || '';
                if (set3Validation.valid && p1s3 > 0 && p2s3 > 0) {
                    this.determineWinner();
                }
            } else {
                this.set3Error = '';
            }
        } else {
            this.set3Error = '';
        }
    },
    determineWinner() {
        if (!this.resultMatch || !this.resultMatch.player1Id || !this.resultMatch.player2Id) return;
        
        const p1s1 = parseInt(this.player1Set1) || 0;
        const p2s1 = parseInt(this.player2Set1) || 0;
        const p1s2 = parseInt(this.player1Set2) || 0;
        const p2s2 = parseInt(this.player2Set2) || 0;
        const p1s3 = parseInt(this.player1Set3) || 0;
        const p2s3 = parseInt(this.player2Set3) || 0;
        
        let player1Sets = 0;
        let player2Sets = 0;
        
        if (p1s1 > 0 && p2s1 > 0) {
            if (p1s1 > p2s1) player1Sets++;
            else player2Sets++;
        }
        if (p1s2 > 0 && p2s2 > 0) {
            if (p1s2 > p2s2) player1Sets++;
            else player2Sets++;
        }
        if (this.set3Required && p1s3 > 0 && p2s3 > 0) {
            if (p1s3 > p2s3) player1Sets++;
            else player2Sets++;
        }
        
        if (player1Sets > player2Sets) {
            this.autoDeterminedWinner = this.resultMatch.player1Id;
            this.autoDeterminedWinnerName = this.resultMatch.team1Name || this.resultMatch.player1 || 'Player 1';
        } else if (player2Sets > player1Sets) {
            this.autoDeterminedWinner = this.resultMatch.player2Id;
            this.autoDeterminedWinnerName = this.resultMatch.team2Name || this.resultMatch.player2 || 'Player 2';
        } else {
            this.autoDeterminedWinner = null;
            this.autoDeterminedWinnerName = '';
        }
        
        this.selectedWinner = this.autoDeterminedWinner;
    },
    canSubmitResult() {
        if (!this.resultMatchId) return false;
        
        const p1s1 = parseInt(this.player1Set1) || 0;
        const p2s1 = parseInt(this.player2Set1) || 0;
        const p1s2 = parseInt(this.player1Set2) || 0;
        const p2s2 = parseInt(this.player2Set2) || 0;
        
        if (p1s1 === 0 || p2s1 === 0 || p1s2 === 0 || p2s2 === 0) return false;
        
        if (this.set1Error && this.set1Error.length > 0) return false;
        if (this.set2Error && this.set2Error.length > 0) return false;
        
        if (this.set3Required) {
            const p1s3 = parseInt(this.player1Set3) || 0;
            const p2s3 = parseInt(this.player2Set3) || 0;
            if (p1s3 === 0 || p2s3 === 0) return false;
            if (this.set3Error && this.set3Error.length > 0) return false;
        }
        
        if (!this.autoDeterminedWinner) return false;
        
        return true;
    },
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
            this.sortTableRows('results-table', this.completedSortOrder);
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
        this.autoDeterminedWinner = null;
        this.autoDeterminedWinnerName = '';
        this.isLateInput = matchData.isLateInput || false;
        this.lateWarning = matchData.lateWarning || '';
        this.set3Required = false;
        this.set3Enabled = false;
        this.set1Error = '';
        this.set2Error = '';
        this.set3Error = '';
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
        this.autoDeterminedWinner = null;
        this.autoDeterminedWinnerName = '';
        this.isLateInput = false;
        this.lateWarning = '';
        this.set3Required = false;
        this.set3Enabled = false;
        this.set1Error = '';
        this.set2Error = '';
        this.set3Error = '';
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
        this.walkoverWinnerConfirmed = false;
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
                    <a href="{{ route('notifications.index') }}" class="text-gray-600 hover:text-gray-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tournament selector dropdown</label>
                        <select 
                            onchange="window.location.href='{{ route('manager.matches') }}?tournament=' + this.value"
                            class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            <option value="">-- Select Tournament --</option>
                            @foreach($allTournaments ?? $tournaments as $tournament)
                                <option value="{{ $tournament->id }}" {{ $selectedTournament && $selectedTournament->id === $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->name }} 
                                    @if($tournament->is_dual_meet)
                                        <span class="text-blue-600">[Dual Meet]</span>
                                    @endif
                                    ({{ ucfirst($tournament->status) }})
                                    @if($tournament->start_date)
                                        – {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(!$selectedTournament)
                        <div class="bg-white rounded-lg shadow p-8 text-center">
                            <p class="text-gray-500 text-lg">Please select a tournament from the dropdown above to view matches.</p>
                        </div>
                    @else
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

                    <!-- Simple tabs powered by real data -->
                    @php
                        $categoryOptions = $categories->mapWithKeys(fn($c) => [$c->id => $c->name]);
                        $activeCategoryId = $selectedCategoryId ? (int)$selectedCategoryId : null;
                        $filteredMatchesByCategory = $matchesByCategory ?? [];
                        if ($activeCategoryId) {
                            $filteredMatchesByCategory = array_filter($filteredMatchesByCategory, fn($_, $key) => (int)$key === $activeCategoryId, ARRAY_FILTER_USE_BOTH);
                        }
                    @endphp

                    <!-- Category filter -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm text-gray-600">Filter by category (affects Ongoing/Brackets/Results):</div>
                        <select onchange="window.location.href='{{ route('manager.matches') }}?tournament={{ $selectedTournament->id }}&category='+this.value" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">All Categories</option>
                            @foreach($categoryOptions as $id => $name)
                                <option value="{{ $id }}" {{ $activeCategoryId === (int)$id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-data="{ tab: 'overview' }" class="space-y-4">
                        <div class="border-b border-gray-200 flex space-x-6">
                            <button @click="tab='overview'" :class="tab==='overview' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'" class="pb-3 text-sm uppercase">Overview</button>
                            <button @click="tab='ongoing'" :class="tab==='ongoing' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'" class="pb-3 text-sm uppercase">Ongoing</button>
                            <button @click="tab='brackets'" :class="tab==='brackets' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'" class="pb-3 text-sm uppercase">Brackets</button>
                            <button @click="tab='schedule'" :class="tab==='schedule' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'" class="pb-3 text-sm uppercase">Schedule</button>
                            <button @click="tab='results'" :class="tab==='results' ? 'border-b-2 border-black text-black font-semibold' : 'text-gray-500'" class="pb-3 text-sm uppercase">Results</button>
                        </div>

                        @php
                            $scheduledCount = isset($matchesByStatus['scheduled']) ? $matchesByStatus['scheduled']->count() : 0;
                            $ongoingCount = isset($matchesByStatus['ongoing']) ? $matchesByStatus['ongoing']->count() : 0;
                            $completedCount = isset($matchesByStatus['completed']) ? $matchesByStatus['completed']->count() : 0;
                            $totalMatches = $scheduledCount + $ongoingCount + $completedCount;
                            $categoriesMap = $selectedTournament->categories->keyBy('id');
                            $formatTeam = function($match) {
                                $p1 = trim(($match->player1?->first_name ?? '') . ' ' . ($match->player1?->last_name ?? ''));
                                if(empty($p1)) {
                                    $p1 = $match->player1?->name ?? 'TBD';
                                }
                                $p2 = trim(($match->player2?->first_name ?? '') . ' ' . ($match->player2?->last_name ?? ''));
                                if(empty($p2)) {
                                    $p2 = $match->player2?->name ?? 'TBD';
                                }
                                $p1p = null;
                                if($match->player1Partner) {
                                    $p1p = trim(($match->player1Partner->first_name ?? '') . ' ' . ($match->player1Partner->last_name ?? ''));
                                    if(empty($p1p)) {
                                        $p1p = $match->player1Partner->name;
                                    }
                                }
                                $p2p = null;
                                if($match->player2Partner) {
                                    $p2p = trim(($match->player2Partner->first_name ?? '') . ' ' . ($match->player2Partner->last_name ?? ''));
                                    if(empty($p2p)) {
                                        $p2p = $match->player2Partner->name;
                                    }
                                }
                                $left = $p1p ? ($p1 . ' / ' . $p1p) : $p1;
                                $right = $p2p ? ($p2 . ' / ' . $p2p) : $p2;
                                return [$left, $right];
                            };
                        @endphp

                        <div x-show="tab==='overview'" x-cloak class="bg-white rounded-lg shadow p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold">Match Overview - {{ $selectedTournament->name }}</h2>
                                @if($selectedTournament->is_dual_meet)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full uppercase">Dual Meet</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-3 gap-4">
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
                            <p class="text-gray-600"><strong>Total Matches:</strong> {{ $totalMatches }}</p>
                            <p class="text-gray-600"><strong>Tournament Status:</strong> {{ ucfirst($selectedTournament->status) }}</p>
                        </div>

                        <div x-show="tab==='ongoing'" x-cloak class="bg-white rounded-lg shadow p-6 space-y-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Ongoing Matches</h2>
                                    <p class="text-sm text-gray-600 mt-1">Matches currently in progress or needing result input</p>
                                </div>
                            </div>
                            @php
                                $ongoingMatches = ($matchesByStatus['ongoing'] ?? collect([]));
                                $scheduledMatches = ($matchesByStatus['scheduled'] ?? collect([]))->filter(function($match) {
                                    if (!$match->scheduled_time || $match->result) {
                                        return false;
                                    }
                                    try {
                                        $matchDateTime = $match->actual_scheduled_date_time;
                                        if (!$matchDateTime) {
                                            return false;
                                        }
                                        $category = $match->category;
                                        $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                                        $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
                                        return \Carbon\Carbon::now()->gte($matchDateTime) && \Carbon\Carbon::now()->lte($matchEndTime->copy()->addHours(2));
                                    } catch (\Exception $e) {
                                        return false;
                                    }
                                });
                                $ongoingMatches = $ongoingMatches->merge($scheduledMatches)->unique('id');
                                $ongoingMatches = $ongoingMatches->filter(function($match) {
                                    return $match->player1_id && $match->player2_id && !$match->result;
                                });
                                $ongoingMatches = $ongoingMatches->sortBy(function($match) {
                                    try {
                                        $dateTime = $match->actual_scheduled_date_time;
                                        if (!$dateTime) {
                                            return '9999-12-31 23:59:59';
                                        }
                                        return $dateTime->format('Y-m-d H:i:s');
                                    } catch (\Exception $e) {
                                        return '9999-12-31 23:59:59';
                                    }
                                })->values();
                            @endphp
                            
                            @if($ongoingMatches->count() > 0)
                                <div class="overflow-x-auto">
                                    <table id="ongoing-table" class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="toggleOngoingSort()">
                                                    Match # <span x-text="ongoingSortOrder === 'asc' ? '▲' : '▼'" class="ml-1"></span>
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Round</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teams</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date & Time</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Court</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($ongoingMatches as $match)
                                                @php 
                                                    [$left,$right] = $formatTeam($match);
                                                    $round = $match->round ?? 'TBD';
                                                    $now = \Carbon\Carbon::now();
                                                    $isCurrentlyOngoing = false;
                                                    $needsResult = false;
                                                    if ($match->scheduled_time) {
                                                        try {
                                                            $matchDateTime = $match->actual_scheduled_date_time;
                                                            if ($matchDateTime) {
                                                                $category = $match->category;
                                                                $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                                                                $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
                                                                $isCurrentlyOngoing = $now->gte($matchDateTime) && $now->lte($matchEndTime);
                                                                $needsResult = $now->gt($matchEndTime);
                                                            }
                                                        } catch (\Exception $e) {
                                                            $needsResult = true;
                                                        }
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
                                                    ];
                                                @endphp
                                                <tr class="hover:bg-gray-50 transition-colors" data-sort-date="{{ $match->actual_scheduled_date_time ? $match->actual_scheduled_date_time->format('Y-m-d\TH:i:s') : '9999-12-31T23:59:59' }}">
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <span class="text-sm font-medium text-gray-900">#{{ $match->match_number ?? $match->id }}</span>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <span class="text-sm font-semibold text-gray-900">{{ $round }}</span>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <span class="text-sm text-gray-900">{{ $categoriesMap[$match->tournament_category_id]->name ?? 'Category' }}</span>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <div class="text-sm text-gray-900">
                                                            <div class="font-medium">{{ $left }}</div>
                                                            <div class="text-gray-500 text-xs mt-0.5">vs</div>
                                                            <div class="font-medium">{{ $right }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            @if($match->actual_scheduled_date)
                                                                <div class="font-medium">{{ $match->actual_scheduled_date->format('M d, Y') }}</div>
                                                                @if($match->scheduled_time)
                                                                    <div class="text-gray-600 text-xs">{{ \Carbon\Carbon::parse($match->scheduled_time)->format('h:i A') }}</div>
                                                                @endif
                                                            @else
                                                                <span class="text-gray-400">TBD</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $match->court_number ? "Court {$match->court_number}" : 'TBD' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        @if($isCurrentlyOngoing)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            In Progress
                                                        </span>
                                                        @elseif($needsResult)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Input Match Result
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                Ongoing
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                        @if($isOwner)
                                                            @if($needsResult || $isCurrentlyOngoing)
                                                                <button 
                                                                    @click="openResultModal({{ $match->id }}, @js($matchData))"
                                                                    class="px-3 py-1.5 bg-[#2C5F4F] hover:bg-[#244D3E] text-white text-xs font-medium rounded transition duration-200 mr-2">
                                                                    Input Result
                                                                </button>
                                                                <button 
                                                                    @click="openWalkoverModal({{ $match->id }}, @js($matchData))"
                                                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition duration-200">
                                                                    Walkover
                                                                </button>
                                                            @endif
                                                        @else
                                                            <span class="text-gray-400 text-xs italic">View only</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No ongoing matches</h3>
                                    <p class="mt-1 text-sm text-gray-500">There are no matches currently in progress.</p>
                                </div>
                            @endif
                        </div>

                        <div x-show="tab==='brackets'" x-cloak class="bg-white rounded-lg shadow p-6 space-y-4">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-bold text-gray-900">Tournament Brackets</h2>
                            </div>
                            @php
                                $matchScoreService = app(\App\Services\MatchScoreService::class);
                                $normalizeRoundName = function($roundName, $category = null, $tournament = null) {
                                    if ($tournament && $tournament->bracket_type === 'round_robin') {
                                        $r = strtolower(trim((string)$roundName));
                                        if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                            return 'Round ' . (int)$m[1];
                                        }
                                        return ucfirst($roundName);
                                    }
                                    
                                    $r = strtolower(trim((string)$roundName));
                                    if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
                                    if (str_contains($r, 'quarter')) return 'Quarterfinals';
                                    if (str_contains($r, 'semi')) return 'Semifinals';
                                    if (str_contains($r, 'final') && !str_contains($r, 'semi')) return 'Finals';
                                    
                                    if ($category && $tournament && is_numeric($roundName)) {
                                        $slots = $category->max_participants ?? 16;
                                        $maxRounds = (int)ceil(log($slots, 2));
                                        return \App\Helpers\TournamentRoundHelper::getRoundName(
                                            'single_elimination',
                                            (int)$roundName,
                                            $maxRounds
                                        );
                                    }
                                    
                                    if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                        $num = (int)$m[1];
                                        return "Round {$num}";
                                    }
                                    return ucfirst($roundName);
                                };
                                $getRoundOrder = function($roundName) {
                                    $r = strtolower(trim((string)$roundName));
                                    if (preg_match('/round of 64/i', $r)) return 64;
                                    if (preg_match('/round of 32/i', $r)) return 32;
                                    if (preg_match('/round of 16/i', $r)) return 16;
                                    if (preg_match('/round of 8/i', $r)) return 8;
                                    if (preg_match('/round of (\d+)/i', $r, $m)) return (int)$m[1];
                                    if (str_contains($r, 'quarter')) return 8;
                                    if (str_contains($r, 'semi')) return 4;
                                    if (str_contains($r, 'final') && !str_contains($r, 'semi')) return 2;
                                    if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                        $roundNum = (int)$m[1];
                                        return 1000 - $roundNum;
                                    }
                                    if (str_contains($r, 'first round')) return 1000;
                                    if (str_contains($r, 'second round')) return 999;
                                    return 0;
                                };
                                $scoreLine = function($m) use ($matchScoreService) {
                                    $res = $m->result;
                                    if (!$res) return null;
                                    $isWalkover = ($res->player1_set1_score === 0 && $res->player2_set1_score === 0) &&
                                                  ($res->player1_set2_score === null || $res->player1_set2_score === 0) &&
                                                  ($res->player2_set2_score === null || $res->player2_set2_score === 0);
                                    if ($isWalkover) {
                                        return ['final_score' => 'Walkover', 'set_scores' => ['set1' => 'Walkover'], 'is_walkover' => true];
                                    }
                                    $finalScore = $matchScoreService->calculateFinalScore($res);
                                    $setScores = $matchScoreService->getFormattedSetScores($res);
                                    return ['final_score' => $finalScore['final_score'], 'set_scores' => $setScores, 'is_walkover' => false];
                                };
                            @endphp

                            @forelse($filteredMatchesByCategory ?? [] as $catId => $rounds)
                                @php
                                    $catName = $categoriesMap[$catId]->name ?? 'Category';
                                    $category = $categoriesMap[$catId] ?? null;
                                    $slots = $category->max_participants ?? 0;
                                    $matchCount = collect($rounds)->flatten()->count();
                                    $normalizedRounds = [];
                                    $isRoundRobin = $selectedTournament && $selectedTournament->bracket_type === 'round_robin';
                                    $enhancedNormalize = function($roundName) use ($normalizeRoundName, $category, $selectedTournament) {
                                        return $normalizeRoundName($roundName, $category, $selectedTournament);
                                    };
                                    foreach ($rounds as $roundName => $matches) {
                                        $normalized = $enhancedNormalize($roundName);
                                        if (!isset($normalizedRounds[$normalized])) {
                                            $normalizedRounds[$normalized] = [];
                                        }
                                        $normalizedRounds[$normalized] = array_merge($normalizedRounds[$normalized], $matches);
                                    }
                                    $isRoundRobin = $selectedTournament && $selectedTournament->bracket_type === 'round_robin';
                                    
                                    // For round robin, filter out elimination rounds and sort numerically (1, 2, 3...)
                                    if ($isRoundRobin) {
                                        // Filter to only include round robin rounds (Round 1, Round 2, etc.)
                                        // Exclude elimination rounds like "Round of 16", "Quarterfinals", "Semifinals", "Finals"
                                        $normalizedRounds = collect($normalizedRounds)->filter(function($matches, $roundName) {
                                            $r = strtolower(trim((string)$roundName));
                                            // Only keep rounds that match "Round {number}" pattern (e.g., "Round 1", "Round 2")
                                            // Exclude elimination round patterns
                                            if (preg_match('/round\s+of\s+\d+/i', $r)) return false; // "Round of 16", etc.
                                            if (str_contains($r, 'quarter')) return false; // "Quarterfinals"
                                            if (str_contains($r, 'semi')) return false; // "Semifinals"
                                            if (str_contains($r, 'final') && !str_contains($r, 'semi')) return false; // "Finals"
                                            // Only allow "Round {number}" format
                                            return preg_match('/^round\s*\d+$/i', $r);
                                        });
                                        
                                        // Sort rounds numerically (1, 2, 3...)
                                        $sortedRounds = $normalizedRounds->sortBy(function($matches, $roundName) {
                                            $r = strtolower(trim((string)$roundName));
                                            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                                return (int)$m[1]; // Sort by round number ascending (1, 2, 3...)
                                            }
                                            return 9999; // Should not happen after filtering, but just in case
                                        });
                                    } else {
                                        $sortedRounds = collect($normalizedRounds)->sortByDesc(function($matches, $roundName) use ($getRoundOrder) {
                                            return $getRoundOrder($roundName);
                                        });
                                    }
                                                                        @endphp
                                <div class="border border-gray-200 rounded-xl mb-6 overflow-hidden shadow-lg bg-gradient-to-br from-gray-50 to-white">
                                    <div class="px-6 py-4 bg-gradient-to-r from-[#2C5F4F] to-[#244D3E] flex justify-between items-center">
                                        <div class="font-bold text-white text-lg">{{ $catName }}</div>
                                        <div class="text-sm text-gray-200">{{ $matchCount }} match{{ $matchCount === 1 ? '' : 'es' }}</div>
                                    </div>
                                    
                                    @if($isRoundRobin)
                                        <div class="p-6">
                                            @foreach($sortedRounds as $roundName => $matches)
                                                <div class="mb-6 last:mb-0">
                                                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-[#2C5F4F]">{{ $roundName }}</h3>
                                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                        @foreach($matches as $match)
                                                            @php [$left,$right] = $formatTeam($match); $sets = $scoreLine($match); @endphp
                                                            <div class="bg-white border-2 {{ $match->status === 'completed' ? 'border-green-400' : ($match->status === 'ongoing' ? 'border-yellow-400' : 'border-gray-300') }} rounded-lg p-4 shadow-sm hover:shadow-md transition-all">
                                                                <div class="flex items-center justify-between mb-3">
                                                                    <span class="text-xs font-semibold px-2 py-1 rounded {{ $match->status === 'completed' ? 'bg-green-100 text-green-800' : ($match->status === 'ongoing' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700') }}">{{ ucfirst($match->status) }}</span>
                                                                    @if($match->match_number)<span class="text-xs text-gray-500">#{{ $match->match_number }}</span>@endif
                                                                </div>
                                                                <div class="space-y-2 mb-3">
                                                                    <div class="font-semibold text-sm {{ $match->winner_id && $match->player1_id === $match->winner_id ? 'text-[#2C5F4F] font-bold' : 'text-gray-900' }}">
                                                                        {{ $left }}{{ $match->winner_id && $match->player1_id === $match->winner_id ? ' (Winner)' : '' }}
                                                                    </div>
                                                                    <div class="text-center text-gray-400 text-xs">vs</div>
                                                                    <div class="font-semibold text-sm {{ $match->winner_id && $match->player2_id === $match->winner_id ? 'text-[#2C5F4F] font-bold' : 'text-gray-900' }}">
                                                                        {{ $right }}{{ $match->winner_id && $match->player2_id === $match->winner_id ? ' (Winner)' : '' }}
                                                                    </div>
                                                                </div>
                                                                @if($sets)
                                                                    <div class="pt-3 border-t border-gray-200">
                                                                        <div class="text-sm font-bold text-[#2C5F4F]">{{ $sets['final_score'] }}</div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                                                        </div>
                                                                    @else
                                        <div class="p-6 overflow-x-auto">
                                            <div class="flex gap-8 min-w-max">
                                                @foreach($sortedRounds as $roundName => $matches)
                                                    @php
                                                        $roundKeys = $sortedRounds->keys()->all();
                                                        $currentIndex = array_search($roundName, $roundKeys);
                                                        $isLastRound = $currentIndex === count($roundKeys) - 1;
                                                    @endphp
                                                    <div class="flex flex-col justify-center {{ $isLastRound ? '' : 'relative' }}" style="min-width: 280px;">
                                                        <div class="mb-4 text-center">
                                                            <h3 class="text-base font-bold text-gray-800 bg-[#2C5F4F] text-white px-4 py-2 rounded-lg inline-block">{{ $roundName }}</h3>
                                                                        </div>
                                                        <div class="space-y-4">
                                                            @foreach($matches as $matchIndex => $match)
                                                                @php [$left,$right] = $formatTeam($match); $sets = $scoreLine($match); @endphp
                                                                <div class="relative">
                                                                    <div class="bg-white border-2 {{ $match->status === 'completed' ? 'border-green-400 shadow-md' : ($match->status === 'ongoing' ? 'border-yellow-400 shadow-md' : 'border-gray-300') }} rounded-lg p-4 shadow-sm hover:shadow-lg transition-all">
                                                                        <div class="flex items-center justify-between mb-2">
                                                                            <span class="text-xs font-semibold px-2 py-1 rounded {{ $match->status === 'completed' ? 'bg-green-100 text-green-800' : ($match->status === 'ongoing' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700') }}">{{ ucfirst($match->status) }}</span>
                                                                            @if($match->match_number)<span class="text-xs text-gray-500">#{{ $match->match_number }}</span>@endif
                                                                        </div>
                                                                        <div class="space-y-2">
                                                                            <div class="font-semibold text-sm {{ $match->winner_id && $match->player1_id === $match->winner_id ? 'text-[#2C5F4F] font-bold' : 'text-gray-900' }}">
                                                                                {{ $left }}{{ $match->winner_id && $match->player1_id === $match->winner_id ? ' (Winner)' : '' }}
                                                                            </div>
                                                                            <div class="text-center text-gray-400 text-xs font-medium">vs</div>
                                                                            <div class="font-semibold text-sm {{ $match->winner_id && $match->player2_id === $match->winner_id ? 'text-[#2C5F4F] font-bold' : 'text-gray-900' }}">
                                                                                {{ $right }}{{ $match->winner_id && $match->player2_id === $match->winner_id ? ' (Winner)' : '' }}
                                                                            </div>
                                                                        </div>
                                                                        @if($sets)
                                                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                                                <div class="text-xs font-bold text-[#2C5F4F]">{{ $sets['final_score'] }}</div>
                                                                                @if(isset($sets['set_scores']) && !$sets['is_walkover'])
                                                                                    <div class="text-xs text-gray-600 mt-1 space-y-0.5">
                                                                                        @foreach(array_slice($sets['set_scores'], 0, 2) as $set => $score)
                                                                                <div>{{ ucfirst(str_replace('set', 'Set ', $set)) }}: {{ $score }}</div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                                    @if(!$isLastRound)
                                                                        <div class="absolute top-1/2 -right-4 w-4 h-0.5 bg-gray-400"></div>
                                                                        @if($matchIndex % 2 === 0)
                                                                            <div class="absolute top-1/2 -right-4 w-0.5 h-16 bg-gray-400"></div>
                                                                            <div class="absolute top-1/2 -right-4 translate-y-8 w-4 h-0.5 bg-gray-400"></div>
                                                                        @endif
                                                                    @endif
                                                    </div>
                                                            @endforeach
                                                        </div>
                                                        </div>
                                                @endforeach
                                                    </div>
                                            </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-gray-500">No brackets generated yet</p>
                                    <p class="text-sm text-gray-400 mt-1">Generate matches first to create the bracket</p>
                                </div>
                            @endforelse
                        </div>

                        <div x-show="tab==='schedule'" x-cloak class="bg-white rounded-lg shadow p-6 space-y-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Match Schedule</h2>
                                    <p class="text-sm text-gray-600 mt-1">Upcoming scheduled matches</p>
                                </div>
                            </div>
                            @php
                                $scheduledMatches = ($matchesByStatus['scheduled'] ?? collect([]))->filter(function($match) {
                                    if (!$match->scheduled_time || $match->result) {
                                        return false;
                                    }
                                    try {
                                        $matchDateTime = $match->actual_scheduled_date_time;
                                        if (!$matchDateTime) {
                                            return false;
                                        }
                                        return \Carbon\Carbon::now()->lt($matchDateTime);
                                    } catch (\Exception $e) {
                                        return false;
                                    }
                                });
                                if($activeCategoryId) {
                                    $scheduledMatches = $scheduledMatches->where('tournament_category_id', $activeCategoryId);
                                }
                                $scheduledMatches = $scheduledMatches->sortBy(function($match) {
                                    if (!$match->scheduled_time) {
                                        return '9999-12-31 23:59:59';
                                    }
                                    try {
                                        $dateTime = $match->actual_scheduled_date_time;
                                        if (!$dateTime) {
                                            return '9999-12-31 23:59:59';
                                        }
                                        return $dateTime->format('Y-m-d H:i:s');
                                    } catch (\Exception $e) {
                                        return '9999-12-31 23:59:59';
                                    }
                                })->values();
                            @endphp
                            
                            @if($scheduledMatches->count() > 0)
                                        <div class="overflow-x-auto">
                                    <table id="schedule-table" class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="toggleScheduleSort()">
                                                    Date & Time <span x-text="scheduleSortOrder === 'asc' ? '▲' : '▼'" class="ml-1"></span>
                                                </th>
                                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Match #</th>
                                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Round</th>
                                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teams</th>
                                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Court</th>
                                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($scheduledMatches as $match)
                                                        @php 
                                                            [$left,$right] = $formatTeam($match);
                                                            $round = $match->round ?? 'TBD';
                                                        @endphp
                                                <tr class="hover:bg-gray-50 transition-colors" data-sort-date="{{ $match->actual_scheduled_date_time ? $match->actual_scheduled_date_time->format('Y-m-d\TH:i:s') : '9999-12-31T23:59:59' }}">
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            @if($match->actual_scheduled_date)
                                                                <div class="font-medium">{{ $match->actual_scheduled_date->format('M d, Y') }}</div>
                                                                @if($match->scheduled_time)
                                                                    <div class="text-gray-600 text-xs">{{ \Carbon\Carbon::parse($match->scheduled_time)->format('h:i A') }}</div>
                                                                @endif
                                                            @else
                                                                <span class="text-gray-400">TBD</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                            <td class="px-4 py-4 whitespace-nowrap">
                                                                <span class="text-sm font-medium text-gray-900">#{{ $match->match_number ?? $match->id }}</span>
                                                            </td>
                                                            <td class="px-4 py-4">
                                                                    <span class="text-sm font-semibold text-gray-900">{{ $round }}</span>
                                                            </td>
                                                            <td class="px-4 py-4 whitespace-nowrap">
                                                                <span class="text-sm text-gray-900">{{ $categoriesMap[$match->tournament_category_id]->name ?? 'Category' }}</span>
                                                            </td>
                                                            <td class="px-4 py-4">
                                                                <div class="text-sm text-gray-900">
                                                                    <div class="font-medium">{{ $left }}</div>
                                                                    <div class="text-gray-500 text-xs mt-0.5">vs</div>
                                                                    <div class="font-medium">{{ $right }}</div>
                                                                </div>
                                                            </td>
                                                            <td class="px-4 py-4 whitespace-nowrap">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                    {{ $match->court_number ? "Court {$match->court_number}" : 'TBD' }}
                                                                </span>
                                                            </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                                @if($isOwner)
                                                                            <button 
                                                                                @click="openRescheduleModal({{ $match->id }}, '{{ $match->actual_scheduled_date ? $match->actual_scheduled_date->format('Y-m-d') : '' }}', '{{ $match->scheduled_time ? \Carbon\Carbon::parse($match->scheduled_time)->format('H:i') : '' }}', '{{ $match->court_number }}')"
                                                                class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded transition duration-200">
                                                                                Reschedule
                                                                            </button>
                                                                @else
                                                            <span class="text-gray-400 text-xs italic">View only</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No scheduled matches</h3>
                                    <p class="mt-1 text-sm text-gray-500">There are no upcoming scheduled matches.</p>
                                </div>
                            @endif
                        </div>

                        <div x-show="tab==='results'" x-cloak class="bg-white rounded-lg shadow p-6 space-y-4">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-bold text-gray-900">Match Results</h2>
                            </div>
                            @php 
                                $normalizeRoundName = function($roundName) {
                                    $r = strtolower(trim((string)$roundName));
                                    if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
                                    if (str_contains($r, 'quarter')) return 'Quarterfinals';
                                    if (str_contains($r, 'semi')) return 'Semifinals';
                                    if (str_contains($r, 'final') && !str_contains($r, 'semi')) return 'Finals';
                                    if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                        $num = (int)$m[1];
                                        if ($num === 1) return 'Round 1';
                                        return "Round {$num}";
                                    }
                                    return ucfirst($roundName);
                                };
                                $allMatchesWithResults = collect([]);
                                if ($selectedTournament) {
                                    $allMatchesWithResults = \App\Models\TournamentMatch::where('tournament_id', $selectedTournament->id)
                                        ->whereHas('result', function($q) {
                                            $q->whereNotNull('winner_id');
                                        })
                                        ->with(['player1', 'player2', 'player1Partner', 'player2Partner', 'category', 'result', 'winner', 'winnerPartner'])
                                        ->get();
                                }
                                if($activeCategoryId){
                                    $allMatchesWithResults = $allMatchesWithResults->where('tournament_category_id', $activeCategoryId);
                                }
                                $completedList = $allMatchesWithResults->map(function($match) use ($normalizeRoundName) {
                                    $match->normalized_round = $normalizeRoundName($match->round ?? 'Round 1');
                                    return $match;
                                })->sortBy(function($match) {
                                    if (!$match->scheduled_time) {
                                        return '9999-12-31 23:59:59';
                                    }
                                    try {
                                        $scheduledTime = $match->scheduled_time instanceof \Carbon\Carbon 
                                            ? $match->scheduled_time 
                                            : \Carbon\Carbon::parse($match->scheduled_time);
                                        $scheduledDate = $match->scheduled_date instanceof \Carbon\Carbon 
                                            ? $match->scheduled_date 
                                            : \Carbon\Carbon::parse($match->scheduled_date);
                                        return $scheduledDate->format('Y-m-d') . ' ' . $scheduledTime->format('H:i:s');
                                    } catch (\Exception $e) {
                                        return '9999-12-31 23:59:59';
                                    }
                                })->values();
                            @endphp
                            @if($completedList->count())
                                <div class="overflow-x-auto">
                                    <table id="results-table" class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="toggleCompletedSort()">
                                                    Match # <span x-text="completedSortOrder === 'asc' ? '▲' : '▼'" class="ml-1"></span>
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Round</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teams</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Winner</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Score</th>
                                        </tr>
                                    </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($completedList as $match)
                                            @php
                                                [$left,$right] = $formatTeam($match);
                                                    $winner = trim(($match->winner?->first_name ?? '') . ' ' . ($match->winner?->last_name ?? ''));
                                                    if(empty($winner)) {
                                                        $winner = $match->winner?->name ?? 'TBD';
                                                    }
                                                    if($match->winner_partner_id && $match->winnerPartner){
                                                        $winnerPartner = trim(($match->winnerPartner->first_name ?? '') . ' ' . ($match->winnerPartner->last_name ?? ''));
                                                        if(empty($winnerPartner)) {
                                                            $winnerPartner = $match->winnerPartner->name ?? '';
                                                        }
                                                    $winner = trim($winner . ' / ' . $winnerPartner);
                                                }
                                                $res = $match->result;
                                                $finalScore = $res ? $matchScoreService->calculateFinalScore($res) : null;
                                                $setScores = $res ? $matchScoreService->getFormattedSetScores($res) : [];
                                            @endphp
                                                <tr class="hover:bg-gray-50 transition-colors" data-sort-date="{{ $match->actual_scheduled_date_time ? $match->actual_scheduled_date_time->format('Y-m-d\TH:i:s') : '9999-12-31T23:59:59' }}">
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <span class="text-sm font-medium text-gray-900">#{{ $match->match_number ?? $match->id }}</span>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <span class="text-sm font-semibold text-gray-900">{{ $match->normalized_round ?? ($match->round ?? 'N/A') }}</span>
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        <span class="text-sm text-gray-900">{{ $categoriesMap[$match->tournament_category_id]->name ?? 'Category' }}</span>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <div class="text-sm text-gray-900">
                                                        <div class="font-medium">{{ $left }}</div>
                                                            <div class="text-gray-500 text-xs mt-0.5">vs</div>
                                                        <div class="font-medium">{{ $right }}</div>
                                                    </div>
                                                </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                    <span class="font-semibold text-[#2C5F4F]">{{ $winner }}</span>
                                                </td>
                                                    <td class="px-4 py-4">
                                                    @if($res && $finalScore)
                                                        <div class="space-y-1">
                                                            <div class="text-sm font-bold text-gray-900">
                                                                Final: {{ $finalScore['final_score'] }}
                                                            </div>
                                                            <div class="text-xs text-gray-700 space-y-0.5">
                                                                @foreach($setScores as $set => $score)
                                                                    <div>{{ ucfirst(str_replace('set', 'Set ', $set)) }}: {{ $score }}</div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No completed matches</h3>
                                    <p class="mt-1 text-sm text-gray-500">There are no completed matches with results yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(false)

                    <div x-show="activeTab === 'ongoing'" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold mb-4">Ongoing Matches - {{ $selectedTournament->name }}</h2>
                        @php
                                // Use matchesByStatus from controller (already filtered and status-updated)
                                $ongoingMatches = isset($matchesByStatus['ongoing']) ? $matchesByStatus['ongoing'] : collect([]);
                                
                                // Sort ongoing matches by date and time (earliest first)
                                $ongoingMatches = $ongoingMatches->sortBy(function($match) {
                                    if (!$match->scheduled_time) {
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
                                                        
                                                        @if($isOwner && $match->player1_id && $match->player2_id && $match->status === 'ongoing')
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
                                                        @elseif($isOwner && $match->status === 'scheduled')
                                                            <button 
                                                                @click="openRescheduleModal({{ $match->id }}, '{{ $match->scheduled_date }}', '{{ $match->scheduled_time }}', '{{ $match->court_number }}')"
                                                                class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded transition duration-200">
                                                                Reschedule
                                                            </button>
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
                                                {{ $category->full_name ?? 'Unknown Category' }}
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
                                                                                <div class="font-medium text-sm flex-1 min-w-0 {{ ($match->result && $match->result->winner_id === $match->player1_id) ? 'text-[#2C5F4F] font-bold' : '' }}">
                                                                                    @php
                                                                                        $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                                                                                        $categoryType = strtolower($match->category->name ?? '');
                                                                                        $isDoublesCategory = str_contains($categoryType, 'doubles') || str_contains($categoryType, 'mixed');
                                                                                        $isPlayer1Winner = ($match->result && $match->result->winner_id === $match->player1_id) || ($match->winner_id && $match->winner_id === $match->player1_id);
                                                                                    @endphp
                                                                                    @if($isDoublesCategory && $match->player1_partner_id && optional($match->player1Partner))
                                                                                        <div class="truncate">
                                                                                            <span>{{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}</span>
                                                                                            <span class="text-gray-500"> & </span>
                                                                                            <span>{{ optional($match->player1Partner)->first_name }} {{ optional($match->player1Partner)->last_name }}</span>
                                                                                            @if($isPlayer1Winner)<span class="ml-1">(Winner)</span>@endif
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="truncate">
                                                                                            {{ optional($match->player1)->first_name ?? 'TBD' }} {{ optional($match->player1)->last_name ?? '' }}@if($isPlayer1Winner) (Winner)@endif
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
                                                                                <div class="font-medium text-sm flex-1 min-w-0 {{ ($match->result && $match->result->winner_id === $match->player2_id) ? 'text-[#2C5F4F] font-bold' : '' }}">
                                                                                    @php
                                                                                        $isPlayer2Winner = ($match->result && $match->result->winner_id === $match->player2_id) || ($match->winner_id && $match->winner_id === $match->player2_id);
                                                                                    @endphp
                                                                                    @if($isDoublesCategory && $match->player2_partner_id && optional($match->player2Partner))
                                                                                        <div class="truncate">
                                                                                            <span>{{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}</span>
                                                                                            <span class="text-gray-500"> & </span>
                                                                                            <span>{{ optional($match->player2Partner)->first_name }} {{ optional($match->player2Partner)->last_name }}</span>
                                                                                            @if($isPlayer2Winner)<span class="ml-1">(Winner)</span>@endif
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="truncate">
                                                                                            {{ optional($match->player2)->first_name ?? 'TBD' }} {{ optional($match->player2)->last_name ?? '' }}@if($isPlayer2Winner) (Winner)@endif
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
                                    if (!$match->scheduled_time) {
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
                                                                $hoursLate = (int)\Carbon\Carbon::now()->diffInHours($matchEndTime);
                                                                $daysLate = (int)\Carbon\Carbon::now()->diffInDays($matchEndTime);
                                                                if ($daysLate > 0) {
                                                                    $lateWarning = "This match ended {$daysLate} day(s) ago. Result pending.";
                                                                } else {
                                                                    $lateWarning = "This match ended {$hoursLate} hour(s) ago. Result pending.";
                                                                }
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
                                                                @if($match->status === 'completed')
                                                                    <span class="text-xs text-gray-500 italic">Match Completed</span>
                                                                @elseif($match->status === 'ongoing')
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
                                                                @elseif($match->status === 'scheduled')
                                                                    <button 
                                                                        @click="openRescheduleModal({{ $match->id }}, '{{ $match->scheduled_date }}', '{{ $match->scheduled_time }}', '{{ $match->court_number }}')"
                                                                        class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded transition duration-200">
                                                                        Reschedule
                                                                    </button>
                                                                @endif
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
                                    if (!$match->scheduled_time) {
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
                    @endif
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

            <form method="POST" :action="`{{ url('/manager/matches') }}/${rescheduleMatchId}/reschedule`" @submit.prevent="showRescheduleConfirm = true" id="reschedule-form">
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
                        type="button"
                        @click="showRescheduleConfirm = true"
                        class="px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-lg font-medium transition duration-200">
                        Confirm Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reschedule Confirmation Modal -->
    <div x-show="showRescheduleConfirm" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4"
         @click.self="showRescheduleConfirm = false"
         x-transition>
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Confirm Reschedule</h3>
                <button @click="showRescheduleConfirm = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <p class="text-gray-700 mb-6">You can only reschedule this match once. Do you want to proceed?</p>
            <div class="flex gap-4 justify-end">
                <button 
                    type="button"
                    @click="showRescheduleConfirm = false"
                    class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium transition duration-200">
                    Cancel
                </button>
                <button 
                    type="button"
                    @click="
                        showRescheduleConfirm = false;
                        document.getElementById('reschedule-form').action = `{{ url('/manager/matches') }}/${rescheduleMatchId}/reschedule`;
                        document.getElementById('reschedule-form').submit();
                    "
                    class="px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-lg font-medium transition duration-200">
                    Yes, Reschedule
                </button>
            </div>
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

            <form method="POST" :action="resultMatchId ? `{{ url('/manager/matches') }}/${resultMatchId}/record-result` : '#'">
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
                                       @input="checkSet3Requirement()"
                                       :class="set1Error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-[#2C5F4F]'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player2"></span>
                                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                                </label>
                                <input type="number" name="player2_set1_score" x-model="player2Set1" min="0" max="30" required
                                       @input="checkSet3Requirement()"
                                       :class="set1Error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-[#2C5F4F]'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                            </div>
                        </div>
                        <p x-show="set1Error" x-text="set1Error" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set 2 Scores <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player1"></span>
                                    <span x-show="resultMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player1Partner"></span></span>
                                </label>
                                <input type="number" name="player1_set2_score" x-model="player1Set2" min="0" max="30" required
                                       @input="checkSet3Requirement()"
                                       :class="set2Error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-[#2C5F4F]'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player2"></span>
                                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                                </label>
                                <input type="number" name="player2_set2_score" x-model="player2Set2" min="0" max="30" required
                                       @input="checkSet3Requirement()"
                                       :class="set2Error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-[#2C5F4F]'"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                            </div>
                        </div>
                        <p x-show="set2Error" x-text="set2Error" class="mt-1 text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Set 3 Scores
                            <span x-show="set3Required" class="text-red-500">*</span>
                            <span x-show="set3Required" class="text-xs text-red-600 font-normal">(Required - Match is tied 1-1)</span>
                            <span x-show="!set3Required && player1Set1 && player2Set1 && player1Set2 && player2Set2" class="text-xs text-gray-500 font-normal">(Disabled - Match decided in 2 sets)</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player1"></span>
                                    <span x-show="resultMatch?.player1Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player1Partner"></span></span>
                                </label>
                                <input type="number" name="player1_set3_score" x-model="player1Set3" min="0" max="30" 
                                       @input="checkSet3Requirement()"
                                       :required="set3Required"
                                       :disabled="!set3Enabled"
                                       :class="set3Error ? 'border-red-500 focus:ring-red-500' : (!set3Enabled ? 'border-gray-200 bg-gray-100 cursor-not-allowed' : 'border-gray-300 focus:ring-[#2C5F4F]')"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">
                                    <span x-text="resultMatch?.player2"></span>
                                    <span x-show="resultMatch?.player2Partner"> <span class="text-gray-500">&</span> <span x-text="resultMatch?.player2Partner"></span></span>
                                </label>
                                <input type="number" name="player2_set3_score" x-model="player2Set3" min="0" max="30"
                                       @input="checkSet3Requirement()"
                                       :required="set3Required"
                                       :disabled="!set3Enabled"
                                       :class="set3Error ? 'border-red-500 focus:ring-red-500' : (!set3Enabled ? 'border-gray-200 bg-gray-100 cursor-not-allowed' : 'border-gray-300 focus:ring-[#2C5F4F]')"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2">
                            </div>
                        </div>
                        <p x-show="set3Error" x-text="set3Error" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    </div>
                    
                <input type="hidden" name="winner_id" :value="autoDeterminedWinner">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Winner (Auto-Determined)</label>
                    <div x-show="autoDeterminedWinner" class="p-4 bg-green-50 border-l-4 border-green-500 rounded-md">
                        <p class="text-sm font-semibold text-green-800">
                            <span x-text="autoDeterminedWinnerName"></span>
                        </p>
                        <p class="text-xs text-green-600 mt-1">Winner automatically determined from set scores</p>
                    </div>
                    <div x-show="!autoDeterminedWinner && player1Set1 && player2Set1 && player1Set2 && player2Set2 && !set3Required" class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-md">
                        <p class="text-sm text-yellow-800">Enter valid set scores to determine winner</p>
                </div>
                    <div x-show="!autoDeterminedWinner && !player1Set1 && !player2Set1 && !player1Set2 && !player2Set2" class="p-4 bg-gray-50 border-l-4 border-gray-400 rounded-md">
                        <p class="text-sm text-gray-600">Winner will be determined automatically after entering set scores</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeResultModal()" 
                            class="px-4 py-2 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!canSubmitResult()"
                            :class="!canSubmitResult() ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#2C5F4F] hover:bg-[#244D3E]'"
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

            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <p class="text-sm text-red-700 font-semibold mb-2">
                    <strong>⚠️ WARNING:</strong> Walkover means the player/team forfeits the match.
                </p>
                <p class="text-sm text-red-700">
                    This results in an <strong>automatic loss</strong> and a <strong>-25 ELO penalty</strong> for the losing player/team. Their ranking will be affected. This action cannot be undone.
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

                <!-- Confirmation Checkbox -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" name="confirmed" x-model="walkoverWinnerConfirmed" value="1"
                               class="w-4 h-4 text-[#C85A54] border-gray-300 rounded focus:ring-[#C85A54] mt-1">
                        <span class="ml-3 text-sm text-gray-700">
                            <strong>I confirm</strong> this walkover action. The loser will receive a <strong>-25 ELO penalty</strong>, and their ranking will be adjusted. This action is final.
                        </span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeWalkoverModal()" 
                            class="px-4 py-2 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!selectedWalkoverWinner || !walkoverWinnerConfirmed"
                            :class="(!selectedWalkoverWinner || !walkoverWinnerConfirmed) ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#C85A54] hover:bg-[#B54A44]'"
                            class="px-4 py-2 text-white font-semibold rounded-lg transition">
                        Mark Walkover & Apply -25 ELO Penalty
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>



