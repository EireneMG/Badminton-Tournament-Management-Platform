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
        </div>
    </div>
</x-dashboard-layout>
