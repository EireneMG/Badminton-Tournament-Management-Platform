<x-dashboard-layout title="Tournament Details">
    <script>
        function withdrawalModalData() {
            return {
                showWithdrawalModal: false,
                selectedRegistration: null,
                withdrawalReason: '',
                customReason: '',
                isDoublesCategory: false,
                openWithdrawalModal(registration, isDoubles) {
                    this.selectedRegistration = registration;
                    this.isDoublesCategory = isDoubles;
                    this.withdrawalReason = '';
                    this.customReason = '';
                    this.showWithdrawalModal = true;
                },
                closeWithdrawalModal() {
                    this.showWithdrawalModal = false;
                    this.selectedRegistration = null;
                    this.withdrawalReason = '';
                    this.customReason = '';
                },
                getWithdrawalReason() {
                    if (this.withdrawalReason === 'other') {
                        if (!this.customReason || this.customReason.trim().length < 20) {
                            return '';
                        }
                        return 'Other: ' + this.customReason.trim();
                    }
                    const reasonMap = {
                        'injury': 'Injury: Unable to participate due to injury or health issues',
                        'schedule_conflict': 'Schedule Conflict: Unable to attend due to conflicting commitments',
                        'personal_matter': 'Personal Matter: Personal or family emergency',
                        'financial': 'Financial Reasons: Unable to pay registration fee'
                    };
                    return reasonMap[this.withdrawalReason] || this.withdrawalReason;
                },
                canSubmitWithdrawal() {
                    if (!this.withdrawalReason) return false;
                    if (this.withdrawalReason === 'other') {
                        return this.customReason && this.customReason.trim().length >= 20;
                    }
                    return true;
                },
                submitWithdrawalForm(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const reason = this.getWithdrawalReason();
                    if (!reason || reason.length < 5) {
                        alert('Please provide a valid withdrawal reason (minimum 5 characters).');
                        return false;
                    }
                    
                    if (!this.selectedRegistration) {
                        alert('Error: Registration ID not found. Please refresh the page and try again.');
                        return false;
                    }
                    
                    const form = event.target.closest('form');
                    if (!form) {
                        console.error('Form not found');
                        alert('Error: Form not found. Please try again.');
                        return false;
                    }
                    
                    // Update the hidden input with the reason
                    const reasonInput = form.querySelector('input[name=reason]');
                    if (reasonInput) {
                        reasonInput.value = reason;
                    } else {
                        console.error('Reason input not found');
                        alert('Error: Form fields not found. Please refresh the page and try again.');
                        return false;
                    }
                    
                    // Update the registration ID
                    const registrationInput = form.querySelector('input[name=tournament_registration_id]');
                    if (registrationInput) {
                        registrationInput.value = this.selectedRegistration;
                    } else {
                        console.error('Registration input not found');
                        alert('Error: Form fields not found. Please refresh the page and try again.');
                        return false;
                    }
                    
                    // Disable submit button to prevent double submission
                    const submitButton = form.querySelector('button[type=submit]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Submitting...</span>';
                    }
                    
                    // Manually submit the form
                    form.submit();
                    return false;
                }
            };
        }
    </script>
    <div class="max-w-7xl mx-auto" x-data="{ 
        ...withdrawalModalData(),
        activeTab: 'overview',
        selectedCategoryFilter: 'all'
    }">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('tournaments.index') }}" class="inline-flex items-center text-[#D4A574] hover:text-[#C4956A]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold text-lg">BACK TO TOURNAMENTS</span>
            </a>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2">
                <!-- Tournament Banner -->
                @if($tournament->banner_path)
                <div class="mb-6 rounded-lg overflow-hidden">
                    <img src="{{ asset('storage/' . $tournament->banner_path) }}" alt="{{ $tournament->name }}" class="w-full h-64 object-cover">
                </div>
                @endif

                <!-- Tab Navigation -->
                @if($tournament->status === 'completed')
                <div class="bg-white rounded-lg mb-6 border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button @click="activeTab = 'overview'" 
                                :class="activeTab === 'overview' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            Overview
                        </button>
                        <button @click="activeTab = 'matches'" 
                                :class="activeTab === 'matches' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            Match History
                        </button>
                    </nav>
                </div>
                @elseif(in_array($tournament->status, ['upcoming', 'ongoing', 'published']))
                <div class="bg-white rounded-lg mb-6 border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button @click="activeTab = 'overview'" 
                                :class="activeTab === 'overview' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            Overview
                        </button>
                        <button @click="activeTab = 'registrations'" 
                                :class="activeTab === 'registrations' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            Player Registrations
                        </button>
                    </nav>
                </div>
                @endif

                <!-- Overview Tab Content -->
                <div x-show="activeTab === 'overview'" x-cloak>
                <!-- Tournament Details -->
                <div class="bg-white rounded-lg p-6 mb-6">
                    <h1 class="text-3xl font-bold mb-4">{{ $tournament->name }}</h1>
                    
                    <div class="space-y-2 mb-6">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span><strong>Tournament Dates:</strong> {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><strong>Venue:</strong> {{ $tournament->venue_name }}</span>
                        </div>

                        @if($tournament->registration_deadline)
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Registration Deadline:</strong> {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d, Y') }} {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('h:i A') }}</span>
                        </div>
                        @endif

                        @if($tournament->withdrawal_deadline)
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Withdrawal Deadline:</strong> {{ \Carbon\Carbon::parse($tournament->withdrawal_deadline)->format('M d, Y') }} {{ \Carbon\Carbon::parse($tournament->withdrawal_deadline)->format('h:i A') }}</span>
                        </div>
                        @endif
                        
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span><strong>Organized by</strong> {{ $tournament->club->name }}</span>
                        </div>

                        @php
                            $fee = $tournament->tournament_fee;
                            if (is_null($fee) || $fee === '' || $fee == 0) {
                                $fee = $tournament->registration_fee ?? 0;
                            }
                        @endphp
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span><strong>Tournament Fee:</strong> ₱{{ number_format($fee, 2) }}</span>
                        </div>
                    </div>

                    @if($tournament->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="font-bold text-lg mb-3">About This Tournament</h3>
                        <div class="text-gray-700 leading-relaxed">
                            {{ $tournament->description }}
                        </div>
                    </div>
                    @endif
                </div>

                </div>
                <!-- End Overview Tab Content -->

                <!-- Player Registrations Tab Content (for Upcoming and Ongoing tournaments) -->
                @if(in_array($tournament->status, ['upcoming', 'ongoing', 'published']) && $tournament->categories->count() > 0)
                <div x-show="activeTab === 'registrations'" x-cloak class="space-y-6">
                    <div class="bg-white rounded-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold">Player Registrations</h2>
                            
                            @if($tournament->categories->count() > 1)
                            <div class="flex items-center gap-3">
                                <label class="text-sm font-medium text-gray-700">Filter by Category:</label>
                                <select x-model="selectedCategoryFilter" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                    <option value="all">All Categories</option>
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
                            <!-- All Categories View -->
                            <div x-show="selectedCategoryFilter === 'all'">
                                @foreach($tournament->categories as $category)
                                    @if(isset($registrationsByCategory[$category->id]) && $registrationsByCategory[$category->id]->count() > 0)
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-[#2C5F4F] mb-3">{{ $category->full_name }}</h3>
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
                            </div>
                            
                            <!-- Individual Category Views -->
                            @foreach($tournament->categories as $category)
                                <div x-show="selectedCategoryFilter == '{{ $category->id }}'" x-cloak>
                                    @if(isset($registrationsByCategory[$category->id]) && $registrationsByCategory[$category->id]->count() > 0)
                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold text-[#2C5F4F] mb-3">{{ $category->full_name }}</h3>
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
                                    @else
                                    <p class="text-gray-500 text-center py-4">No players registered for this category yet.</p>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 text-center py-4">No players registered yet.</p>
                        @endif
                    </div>
                </div>
                @endif
                <!-- End Player Registrations Tab Content -->

                <!-- Match History Tab Content (for Completed tournaments) -->
                @if($tournament->status === 'completed' && $tournament->categories->count() > 0)
                <div x-show="activeTab === 'matches'" x-cloak class="space-y-6">
                @php
                    $firstCategoryWithMatches = $tournament->categories->first(function($cat) use ($matchesByCategory) {
                        return isset($matchesByCategory[$cat->id]) && count($matchesByCategory[$cat->id]) > 0;
                    });
                    $defaultCategoryId = $firstCategoryWithMatches ? $firstCategoryWithMatches->id : ($tournament->categories->first()->id ?? null);
                @endphp
                <div class="bg-white rounded-lg p-6 mb-6" x-data="{ selectedCategoryId: {{ $defaultCategoryId ?? 'null' }} }">
                    <h2 class="text-2xl font-bold mb-4">Match History</h2>
                    
                    @if($tournament->categories->count() > 1)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Category</label>
                        <select x-model="selectedCategoryId" class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                            @foreach($tournament->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    @foreach($tournament->categories as $category)
                        <div x-show="selectedCategoryId == {{ $category->id }}" x-cloak class="space-y-6">
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
                                    $slots = $category->max_participants ?? 0;
                                    $isRoundRobin = $tournament->bracket_type === 'round_robin';
                                    $roundOrder = function($roundName) use ($isRoundRobin) {
                                        if ($isRoundRobin) {
                                            $r = strtolower(trim((string) $roundName));
                                            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                                return (int)$m[1];
                                            }
                                            return 9999;
                                        }
                                        
                                        $r = strtolower(trim((string) $roundName));
                                        if (preg_match('/round of 64/i', $r)) return 64;
                                        if (preg_match('/round of 32/i', $r)) return 32;
                                        if (preg_match('/round of 16/i', $r)) return 16;
                                        if (preg_match('/round of 8/i', $r)) return 8;
                                        if (preg_match('/round of (\d+)/i', $r, $m)) return (int)$m[1];
                                        if (str_contains($r, 'quarter')) return 8;
                                        if (str_contains($r, 'semi')) return 4;
                                        if (str_contains($r, 'final')) return 2;
                                        if (preg_match('/round\s+(\d+)/i', $r, $m)) return 1000 - (int)$m[1];
                                        return 0;
                                    };
                                    $roundLabel = function($roundName, $match = null) use ($slots, $isRoundRobin, $tournament) {
                                        if ($isRoundRobin) {
                                            $r = strtolower(trim((string) $roundName));
                                            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                                                return 'Round ' . (int)$m[1];
                                            }
                                            return ucfirst($roundName);
                                        }
                                        
                                        if ($match && $match->category && is_numeric($roundName)) {
                                            $categorySlots = $match->category?->max_participants ?? $slots;
                                            $maxRounds = (int)ceil(log($categorySlots, 2));
                                            return \App\Helpers\TournamentRoundHelper::getRoundName(
                                                'single_elimination',
                                                (int)$roundName,
                                                $maxRounds
                                            );
                                        }
                                        $r = strtolower(trim((string) $roundName));
                                        if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
                                        if (str_contains($r, 'quarter')) return 'Quarterfinals';
                                        if (str_contains($r, 'semi')) return 'Semifinals';
                                        if (str_contains($r, 'final')) return 'Finals';
                                        $idx = null;
                                        if (preg_match('/round\s+(\d+)/i', $r, $m)) {
                                            $idx = (int)$m[1];
                                        } elseif (preg_match('/^(\d+)$/', $r, $m)) {
                                            $idx = (int)$m[1];
                                        }
                                        if ($idx !== null) {
                                            $progression = \App\Helpers\TournamentRoundHelper::getRoundProgressionForBracketSize($slots);
                                            if (!empty($progression) && isset($progression[$idx - 1])) {
                                                return $progression[$idx - 1];
                                            }
                                        }
                                        return ucfirst($roundName);
                                    };
                                    $full = function($user) {
                                        if (!$user) return null;
                                        if (!empty($user->full_name)) return $user->full_name;
                                        $first = trim($user->first_name ?? '');
                                        $last = trim($user->last_name ?? '');
                                        return trim($first . ' ' . $last) ?: null;
                                    };
                                    if ($isRoundRobin) {
                                        usort($rounds, function($a, $b) use ($roundOrder) {
                                            return $roundOrder($a) <=> $roundOrder($b);
                                        });
                                    } else {
                                        usort($rounds, function($a, $b) use ($roundOrder) {
                                            return $roundOrder($b) <=> $roundOrder($a);
                                        });
                                    }
                                    $matchScoreService = app(\App\Services\MatchScoreService::class);
                                @endphp
                                
                                
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Round</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Match #</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Player/Team 1</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">vs</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Player/Team 2</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Score</th>
                                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($rounds as $roundName)
                                                    @if(isset($matchesByCategory[$category->id][$roundName]))
                                                        @foreach($matchesByCategory[$category->id][$roundName] as $match)
                                                            @php
                                                                $teamA = array_filter([$full($match->player1), $full($match->player1Partner)]);
                                                                $teamB = array_filter([$full($match->player2), $full($match->player2Partner)]);
                                                                $teamAStr = count($teamA) ? implode(' & ', $teamA) : 'TBD';
                                                                $teamBStr = count($teamB) ? implode(' & ', $teamB) : 'TBD';
                                                                $finalScore = null;
                                                                if ($match->result) {
                                                                    $finalScore = $matchScoreService->calculateFinalScore($match->result);
                                                                }
                                                                $isPlayer1Winner = $match->winner_id && ($match->player1_id === $match->winner_id || ($match->player1_partner_id && $match->player1_partner_id === $match->winner_id));
                                                                $isPlayer2Winner = $match->winner_id && ($match->player2_id === $match->winner_id || ($match->player2_partner_id && $match->player2_partner_id === $match->winner_id));
                                                            @endphp
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-3 whitespace-nowrap">
                                                                    <span class="text-sm font-medium text-gray-900">{{ $roundLabel($roundName, $match) }}</span>
                                                                </td>
                                                                <td class="px-4 py-3 whitespace-nowrap">
                                                                    <span class="text-sm text-gray-600">#{{ $match->match_number ?? '-' }}</span>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <span class="text-sm {{ $isPlayer1Winner ? 'text-[#2C5F4F] font-bold' : 'text-gray-900' }}">
                                                                        {{ $teamAStr }}
                                                                        @if($isPlayer1Winner) <span class="text-xs">(Winner)</span>@endif
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-3 text-center">
                                                                    <span class="text-sm text-gray-400 font-medium">vs</span>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <span class="text-sm {{ $isPlayer2Winner ? 'text-[#2C5F4F] font-bold' : 'text-gray-900' }}">
                                                                        {{ $teamBStr }}
                                                                        @if($isPlayer2Winner) <span class="text-xs">(Winner)</span>@endif
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-3 text-center">
                                                                    @if($finalScore)
                                                                        <span class="text-sm font-bold text-[#2C5F4F]">{{ $finalScore['final_score'] }}</span>
                                                                    @else
                                                                        <span class="text-sm text-gray-400">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-3 text-center">
                                                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $match->status === 'completed' ? 'bg-green-100 text-green-800' : ($match->status === 'ongoing' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700') }}">
                                                                        {{ ucfirst($match->status) }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No matches recorded for this category.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                </div>
                @endif

                <!-- End Match History Tab Content -->
            </div>

            <!-- Categories Sidebar -->
            <div class="col-span-1" x-show="{{ $tournament->status === 'completed' ? "activeTab === 'overview'" : 'true' }}" x-cloak>
                <div class="bg-white rounded-lg p-6 sticky top-6">
                    <h3 class="text-xl font-bold mb-4">Categories</h3>
                    
                    @if(!$isEligible)
                    <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> You must be an approved member of a club to register for tournaments.
                        </p>
                        <a href="{{ route('clubs.index') }}" class="text-sm text-yellow-900 font-semibold underline mt-2 inline-block">
                            Find a Club
                        </a>
                    </div>
                    @endif
                    
                    @php
                        $hasSameDateRegistration = \App\Models\TournamentRegistration::where('player_id', auth()->id())
                            ->where('status', 'approved')
                            ->whereHas('tournament', function($query) use ($tournament) {
                                $query->where('start_date', $tournament->start_date)
                                      ->where('id', '!=', $tournament->id)
                                      ->whereIn('status', ['published', 'upcoming', 'ongoing']);
                            })
                            ->exists();
                    @endphp
                    
                    @if($hasSameDateRegistration)
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded">
                        <p class="text-sm text-red-800">
                            <strong>Cannot Register:</strong> You are already registered for an upcoming tournament on this date.
                        </p>
                    </div>
                    @endif

                    <div class="space-y-3">
                        @forelse($tournament->categories as $category)
                        @php
                            $categoryRegistration = $playerRegistration && $playerRegistration->category_id === $category->id ? $playerRegistration : null;
                            $canRegisterCategory = $canRegister && !$categoryRegistration;
                            $canWithdrawCategory = $categoryRegistration && $canWithdraw;
                            $isDoublesCategory = str_contains(strtolower($category->name), 'doubles') || str_contains(strtolower($category->name), 'mixed');
                            $hasTeamPartner = $categoryRegistration && $categoryRegistration->partner_id;
                            
                            $playerGender = strtolower(auth()->user()->gender ?? '');
                            $categoryType = strtoupper($category->type ?? '');
                            
                            $isMenCategory = false;
                            $isWomenCategory = false;
                            $isMixedCategory = ($categoryType === 'XD');
                            
                            if ($categoryType === 'MS' || $categoryType === 'MD') {
                                $isMenCategory = true;
                            } elseif ($categoryType === 'WS' || $categoryType === 'WD') {
                                $isWomenCategory = true;
                            }
                            
                            $isGenderEligible = true;
                            $genderReason = '';
                            
                            if ($isMenCategory && !$isMixedCategory && $playerGender !== 'male') {
                                $isGenderEligible = false;
                                $genderReason = 'Cannot join tournament - This category is for men only';
                            } elseif ($isWomenCategory && !$isMixedCategory && $playerGender !== 'female') {
                                $isGenderEligible = false;
                                $genderReason = 'Cannot join tournament - This category is for women only';
                            }
                            
                            if (!$isGenderEligible) {
                                $canRegisterCategory = false;
                            } else {
                                $canRegisterCategory = $canRegisterCategory && $isGenderEligible;
                            }
                            
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-700',
                                'eligible' => 'bg-blue-100 text-blue-700',
                                'approved' => 'bg-[#2C5F4F] text-white',
                                'rejected' => 'bg-red-100 text-red-700',
                                'withdrawn' => 'bg-gray-100 text-gray-700'
                            ];
                        @endphp
                        
                        <div class="border-2 border-[#D4A574] rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">{{ $category->full_name }}</span>
                                <span class="text-sm text-[#2C5F4F] font-semibold">{{ $category->match_type }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">{{ $category->approved_count ?? 0 }} Registered</p>
                            
                            @if($categoryRegistration)
                            <div class="mb-3 p-3 rounded {{ $statusColors[$categoryRegistration->status] ?? 'bg-gray-100 text-gray-700' }}">
                                <span class="text-xs font-semibold">Status: {{ ucfirst(str_replace('_', ' ', $categoryRegistration->status)) }}</span>
                                @if($categoryRegistration->status === 'withdrawn' && $isDoublesCategory && $hasTeamPartner)
                                    <p class="text-xs text-gray-600 mt-1">You and your partner have been withdrawn from this category.</p>
                                @endif
                            </div>
                            @endif

                            <div class="space-y-2">
                                @if(!$categoryRegistration && !$isGenderEligible)
                                <button disabled class="block w-full bg-gray-300 text-gray-600 px-4 py-2 rounded text-sm font-semibold text-center cursor-not-allowed" title="{{ $genderReason }}">
                                    Cannot join tournament
                                </button>
                                @elseif($canRegisterCategory && $isGenderEligible)
                                <a href="{{ route('player.tournaments.register.show', ['tournament' => $tournament->id, 'category' => $category->id]) }}"
                                    class="block w-full bg-[#C85A54] text-white px-4 py-2 rounded text-sm hover:bg-[#B54A44] transition font-semibold text-center">
                                    Register for {{ $category->full_name }}
                                </a>
                                @elseif(!$categoryRegistration)
                                <button disabled class="block w-full bg-gray-300 text-gray-600 px-4 py-2 rounded text-sm font-semibold text-center cursor-not-allowed">
                                    Cannot register
                                </button>
                                @endif

                                @if($categoryRegistration && $categoryRegistration->status === 'withdrawn')
                                    <p class="text-xs text-gray-500 text-center py-2">You have withdrawn from this category and cannot re-register.</p>
                                @elseif($canWithdrawCategory)
                                <button 
                                    type="button"
                                    @click.prevent="openWithdrawalModal({{ $categoryRegistration->id }}, {{ $isDoublesCategory && $hasTeamPartner ? 'true' : 'false' }})"
                                    class="w-full bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700 transition font-semibold">
                                    {{ ($isDoublesCategory && $hasTeamPartner) ? 'Request Team Withdrawal' : 'Request Withdrawal' }}
                                </button>
                                @elseif($categoryRegistration)
                                    @php
                                        $hasPendingWithdrawal = \App\Models\WithdrawalRequest::where('tournament_registration_id', $categoryRegistration->id)
                                            ->where('status', 'pending')
                                            ->exists();
                                        $deadlinePassed = $tournament->withdrawal_deadline && \Carbon\Carbon::now()->isAfter($tournament->withdrawal_deadline);
                                        $tournamentStarted = \Carbon\Carbon::now()->isAfter($tournament->start_date);
                                    @endphp
                                    @if($hasPendingWithdrawal)
                                    <button 
                                        type="button"
                                        disabled
                                        class="w-full bg-yellow-500 text-white px-4 py-2 rounded text-sm cursor-not-allowed transition font-semibold">
                                        Withdrawal Request Pending
                                    </button>
                                    @elseif($deadlinePassed)
                                    <button 
                                        type="button"
                                        disabled
                                        title="You can no longer withdraw from this tournament. The withdrawal deadline has passed."
                                        class="w-full bg-gray-400 text-white px-4 py-2 rounded text-sm cursor-not-allowed transition font-semibold">
                                        Withdrawal Deadline Passed
                                    </button>
                                    @elseif($tournamentStarted)
                                    <button 
                                        type="button"
                                        disabled
                                        title="The tournament has started. Withdrawals are no longer allowed."
                                        class="w-full bg-gray-400 text-white px-4 py-2 rounded text-sm cursor-not-allowed transition font-semibold">
                                        Tournament Started
                                    </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 text-center py-4">No categories available</p>
                        @endforelse
                    </div>

                    @if(!$canRegister && $isEligible)
                    <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded text-sm text-gray-600">
                        Registration deadline has passed or tournament has started.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Withdrawal Request Modal -->
        <div x-show="showWithdrawalModal" 
             x-cloak
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="withdrawal-modal-title" 
             role="dialog" 
             aria-modal="true"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="closeWithdrawalModal()"></div>

                <!-- Modal panel -->
                <div
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <!-- Modal Header -->
                    <div class="bg-[#C85A54] px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-white" id="withdrawal-modal-title">
                                Request Withdrawal
                            </h3>
                            <button @click="closeWithdrawalModal()" class="text-white hover:text-gray-200 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-5">
                        <!-- Doubles/Mixed Doubles Warning -->
                        <div x-show="isDoublesCategory" class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-800 mb-1">
                                        Team Withdrawal Notice
                                    </p>
                                    <p class="text-sm text-yellow-800">
                                        <strong>Important:</strong> If you withdraw from this doubles/mixed doubles category, both you and your partner will be automatically withdrawn from the tournament. Your partner will be notified of the withdrawal.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Withdrawal Reason Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Select Withdrawal Reason <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="withdrawalReason === 'injury' ? 'border-[#C85A54] bg-red-50' : 'border-gray-200'">
                                    <input type="radio" x-model="withdrawalReason" value="injury" class="mt-1 mr-3 text-[#C85A54] focus:ring-[#C85A54]">
                                    <div>
                                        <div class="font-medium text-gray-900">Injury</div>
                                        <div class="text-sm text-gray-600">Unable to participate due to injury or health issues</div>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="withdrawalReason === 'schedule_conflict' ? 'border-[#C85A54] bg-red-50' : 'border-gray-200'">
                                    <input type="radio" x-model="withdrawalReason" value="schedule_conflict" class="mt-1 mr-3 text-[#C85A54] focus:ring-[#C85A54]">
                                    <div>
                                        <div class="font-medium text-gray-900">Schedule Conflict</div>
                                        <div class="text-sm text-gray-600">Unable to attend due to conflicting commitments</div>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="withdrawalReason === 'personal_matter' ? 'border-[#C85A54] bg-red-50' : 'border-gray-200'">
                                    <input type="radio" x-model="withdrawalReason" value="personal_matter" class="mt-1 mr-3 text-[#C85A54] focus:ring-[#C85A54]">
                                    <div>
                                        <div class="font-medium text-gray-900">Personal Matter</div>
                                        <div class="text-sm text-gray-600">Personal or family emergency</div>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="withdrawalReason === 'financial' ? 'border-[#C85A54] bg-red-50' : 'border-gray-200'">
                                    <input type="radio" x-model="withdrawalReason" value="financial" class="mt-1 mr-3 text-[#C85A54] focus:ring-[#C85A54]">
                                    <div>
                                        <div class="font-medium text-gray-900">Financial Reasons</div>
                                        <div class="text-sm text-gray-600">Unable to pay registration fee</div>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                                       :class="withdrawalReason === 'other' ? 'border-[#C85A54] bg-red-50' : 'border-gray-200'">
                                    <input type="radio" x-model="withdrawalReason" value="other" class="mt-1 mr-3 text-[#C85A54] focus:ring-[#C85A54]">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">Other</div>
                                        <div class="text-sm text-gray-600 mb-2">Please specify your reason below</div>
                                        <textarea 
                                            x-show="withdrawalReason === 'other'"
                                            x-model="customReason"
                                            placeholder="Please provide details about your withdrawal reason (minimum 20 characters)..."
                                            rows="3"
                                            minlength="20"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C85A54] focus:border-transparent text-sm"
                                            :required="withdrawalReason === 'other'"></textarea>
                                        <p x-show="withdrawalReason === 'other' && customReason && customReason.trim().length < 20" class="text-xs text-red-600 mt-1">
                                            Please provide at least 20 characters for your withdrawal reason.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button @click="closeWithdrawalModal()" 
                                class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <form id="withdrawal-form" action="{{ route('withdrawal.store') }}" method="POST" class="inline" 
                              @submit.prevent="submitWithdrawalForm($event)">
                            @csrf
                            <input type="hidden" name="tournament_registration_id" x-bind:value="selectedRegistration">
                            <input type="hidden" name="reason" id="withdrawal-reason-input" x-bind:value="getWithdrawalReason()">
                            <button type="submit" 
                                    :disabled="!canSubmitWithdrawal()"
                                    :class="!canSubmitWithdrawal() ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#C85A54] hover:bg-[#B54A44]'"
                                    class="px-6 py-2.5 text-white font-semibold rounded-lg transition flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Submit Withdrawal Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
