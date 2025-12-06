<x-dashboard-layout title="Tournament Details">
    <div class="max-w-7xl mx-auto" x-data="{ 
        showRegistrationModal: false,
        showWithdrawalModal: false,
        selectedCategory: null,
        selectedRegistration: null,
        withdrawalReason: '',
        customReason: '',
        isDoublesCategory: false,
        selectCategory(category) {
            this.selectedCategory = category;
            this.showRegistrationModal = true;
        },
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
                return this.customReason;
            }
            return this.withdrawalReason;
        }
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
                            <span><strong>Registration Deadline:</strong> {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif

                        @if($tournament->withdrawal_deadline)
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Withdrawal Deadline:</strong> {{ \Carbon\Carbon::parse($tournament->withdrawal_deadline)->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                        
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Organized by {{ $tournament->club->name }}</span>
                        </div>

                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Registration Deadline: {{ \Carbon\Carbon::parse($tournament->registration_deadline)->format('M d, Y') }}</span>
                        </div>

                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Tournament Fee: ₱{{ number_format($tournament->tournament_fee, 2) }}</span>
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

                <!-- Payment Information (Manual Payment System) -->
                @if($playerRegistration && in_array($playerRegistration->status, ['eligible', 'awaiting_payment']))
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mr-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="font-bold text-blue-900 text-lg mb-2">Payment Instructions</h3>
                            <p class="text-blue-800 mb-4">Please pay the tournament fee of ₱{{ number_format($tournament->tournament_fee, 2) }} and contact the manager using the information below:</p>
                            
                            <div class="bg-white rounded-lg p-4 border-2 border-[#D4A574]">
                                <h4 class="font-semibold text-gray-900 mb-3 text-sm">Manager Contact Information</h4>
                                <div class="space-y-3">
                                    @if($tournament->contact_phone)
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                        </svg>
                                        <div>
                                            <span class="text-xs font-semibold text-gray-600">Contact Number</span>
                                            <p class="text-gray-900 font-medium">{{ $tournament->contact_phone }}</p>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($tournament->contact_email)
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                        </svg>
                                        <div>
                                            <span class="text-xs font-semibold text-gray-600">Email Address</span>
                                            <p class="text-gray-900 font-medium break-all">{{ $tournament->contact_email }}</p>
                                        </div>
                                    </div>
                                    @endif

                                    @if($tournament->facebook_link)
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-xs font-semibold text-gray-600">Messenger / Facebook</span>
                                            <p class="text-gray-900 font-medium truncate">
                                                <a href="{{ $tournament->facebook_link }}" target="_blank" class="text-[#C85A54] hover:text-[#B54A44] hover:underline">
                                                    {{ $tournament->facebook_link }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <p class="text-blue-700 text-sm mt-4">
                                <strong>Note:</strong> After making your payment, contact the tournament manager using the information above to confirm your payment. The manager will update your registration status once payment is verified.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Categories Sidebar -->
            <div class="col-span-1">
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

                    <div class="space-y-3">
                        @forelse($tournament->categories as $category)
                        @php
                            $categoryRegistration = $playerRegistration && $playerRegistration->category_id === $category->id ? $playerRegistration : null;
                            $canRegisterCategory = $canRegister && !$categoryRegistration;
                            $canWithdrawCategory = $categoryRegistration && $canWithdraw;
                            $isDoublesCategory = str_contains(strtolower($category->name), 'doubles') || str_contains(strtolower($category->name), 'mixed');
                            $hasTeamPartner = $categoryRegistration && $categoryRegistration->partner_id;
                            
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-700',
                                'eligible' => 'bg-blue-100 text-blue-700',
                                'awaiting_payment' => 'bg-yellow-100 text-yellow-700',
                                'paid' => 'bg-green-100 text-green-700',
                                'approved' => 'bg-[#2C5F4F] text-white',
                                'rejected' => 'bg-red-100 text-red-700'
                            ];
                        @endphp
                        
                        <div class="border-2 border-[#D4A574] rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">{{ $category->name }}</span>
                                <span class="text-sm text-[#2C5F4F] font-semibold">{{ $category->match_type }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">{{ $category->approved_count ?? 0 }} Registered</p>
                            
                            @if($categoryRegistration)
                            <div class="mb-3 p-3 rounded {{ $statusColors[$categoryRegistration->status] ?? 'bg-gray-100 text-gray-700' }}">
                                <span class="text-xs font-semibold">Status: {{ ucfirst(str_replace('_', ' ', $categoryRegistration->status)) }}</span>
                            </div>
                            @endif

                            <div class="space-y-2">
                                @if($canRegisterCategory)
                                <button 
                                    @click="selectCategory({
                                        id: {{ $category->id }},
                                        name: '{{ addslashes($category->name) }}',
                                        match_type: '{{ $category->match_type }}',
                                        slots: {{ $category->slots ?? 0 }},
                                        approved_count: {{ $category->approved_count ?? 0 }},
                                        min_age: {{ $category->min_age ?? 'null' }},
                                        max_age: {{ $category->max_age ?? 'null' }},
                                        skill_level: '{{ $category->skill_level ?? 'all' }}'
                                    })"
                                    class="w-full bg-[#C85A54] text-white px-4 py-2 rounded text-sm hover:bg-[#B54A44] transition font-semibold">
                                    Register for {{ $category->name }}
                                </button>
                                @endif

                                @if($canWithdrawCategory)
                                <button 
                                    type="button"
                                    @click="openWithdrawalModal({{ $categoryRegistration->id }}, {{ $isDoublesCategory && $hasTeamPartner ? 'true' : 'false' }})"
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

        <!-- Registration Confirmation Modal -->
        <div x-show="showRegistrationModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showRegistrationModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showRegistrationModal = false"></div>

                <!-- Modal panel -->
                <div x-show="showRegistrationModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <!-- Modal Header -->
                    <div class="bg-[#2C5F4F] px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-white" id="modal-title">
                                Confirm Registration
                            </h3>
                            <button @click="showRegistrationModal = false" class="text-white hover:text-gray-200 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-5">
                        <!-- Tournament Summary -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-[#2C5F4F] uppercase tracking-wide mb-3">Tournament Details</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h5 class="font-bold text-lg text-gray-900 mb-2">{{ $tournament->name }}</h5>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $tournament->venue_name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-[#D4A574]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>Organized by {{ $tournament->club->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Being Registered For -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-[#2C5F4F] uppercase tracking-wide mb-3">Category</h4>
                            <div class="bg-[#D4A574] bg-opacity-10 rounded-lg p-4 border-2 border-[#D4A574]">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900" x-text="selectedCategory?.name"></span>
                                    <span class="px-3 py-1 bg-[#2C5F4F] text-white text-xs font-semibold rounded-full" x-text="selectedCategory?.match_type"></span>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    <span x-text="selectedCategory?.approved_count || 0"></span> / <span x-text="selectedCategory?.slots || 'Unlimited'"></span> slots filled
                                </div>
                            </div>
                        </div>

                        <!-- Eligibility Status -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-[#2C5F4F] uppercase tracking-wide mb-3">Eligibility Status</h4>
                            <div class="space-y-3">
                                <!-- Club Membership -->
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Club Membership</span>
                                    </div>
                                    <span class="text-sm text-green-600 font-semibold">Verified</span>
                                </div>

                                <!-- Age Requirement -->
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Age Requirement</span>
                                    </div>
                                    <span class="text-sm text-gray-600">
                                        <template x-if="selectedCategory?.min_age && selectedCategory?.max_age">
                                            <span x-text="selectedCategory?.min_age + ' - ' + selectedCategory?.max_age + ' years'"></span>
                                        </template>
                                        <template x-if="selectedCategory?.min_age && !selectedCategory?.max_age">
                                            <span x-text="selectedCategory?.min_age + '+ years'"></span>
                                        </template>
                                        <template x-if="!selectedCategory?.min_age && selectedCategory?.max_age">
                                            <span x-text="'Under ' + selectedCategory?.max_age + ' years'"></span>
                                        </template>
                                        <template x-if="!selectedCategory?.min_age && !selectedCategory?.max_age">
                                            <span class="text-green-600 font-semibold">Open to all ages</span>
                                        </template>
                                    </span>
                                </div>

                                <!-- Skill Level -->
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Skill Level</span>
                                    </div>
                                    <span class="text-sm text-gray-600 capitalize" x-text="selectedCategory?.skill_level === 'all' ? 'All Levels' : selectedCategory?.skill_level"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Fee -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-[#2C5F4F] uppercase tracking-wide mb-3">Registration Fee</h4>
                            <div class="bg-[#C85A54] bg-opacity-10 rounded-lg p-4 border border-[#C85A54]">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-medium">Tournament Fee</span>
                                    <span class="text-2xl font-bold text-[#C85A54]">₱{{ number_format($tournament->tournament_fee, 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Payment will be collected after registration approval</p>
                            </div>
                        </div>

                        <!-- Important Notice -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm text-yellow-800">
                                    By confirming, you agree to participate in this tournament and pay the registration fee upon approval.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button @click="showRegistrationModal = false" 
                                class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <form action="{{ route('player.tournaments.register') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
                            <input type="hidden" name="category_id" x-bind:value="selectedCategory?.id">
                            <button type="submit" 
                                    class="px-5 py-2.5 bg-[#C85A54] text-white font-semibold rounded-lg hover:bg-[#B54A44] transition flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Confirm Registration
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal Request Modal -->
        <div x-show="showWithdrawalModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="withdrawal-modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showWithdrawalModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="closeWithdrawalModal()"></div>

                <!-- Modal panel -->
                <div x-show="showWithdrawalModal"
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
                                <p class="text-sm text-yellow-800">
                                    <strong>Important:</strong> If one player withdraws, the entire team will be fully withdrawn from the tournament.
                                </p>
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
                                            placeholder="Please provide details about your withdrawal reason..."
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#C85A54] focus:border-transparent text-sm"
                                            required></textarea>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button @click="closeWithdrawalModal()" 
                                class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <form action="{{ route('withdrawal.store') }}" method="POST" class="inline" 
                              x-bind:onsubmit="'return withdrawalReason && (withdrawalReason !== \"other\" || customReason.trim().length >= 20);'">
                            @csrf
                            <input type="hidden" name="tournament_registration_id" x-bind:value="selectedRegistration">
                            <input type="hidden" name="reason" x-bind:value="getWithdrawalReason()">
                            <button type="submit" 
                                    :disabled="!withdrawalReason || (withdrawalReason === 'other' && !customReason.trim())"
                                    :class="(!withdrawalReason || (withdrawalReason === 'other' && !customReason.trim())) ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#C85A54] hover:bg-[#B54A44]'"
                                    class="px-5 py-2.5 text-white font-semibold rounded-lg transition flex items-center">
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
