<x-dashboard-layout title="Tournament Details">
    <div class="max-w-7xl mx-auto">
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
                            <span>{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $tournament->venue_name }}</span>
                        </div>
                        
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
    </div>
</x-dashboard-layout>
