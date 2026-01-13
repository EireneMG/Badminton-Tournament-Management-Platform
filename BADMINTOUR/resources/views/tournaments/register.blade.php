<x-dashboard-layout title="Register for Tournament">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="inline-flex items-center text-[#D4A574] hover:text-[#C4956A]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold text-lg">BACK TO TOURNAMENT</span>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <h1 class="text-3xl font-bold text-gray-900 mb-2">Register for {{ $category->full_name }}</h1>
            <p class="text-gray-600 mb-8">{{ $tournament->name }}</p>

            <!-- Category Details -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-[#2C5F4F] mb-4">Category Information</h2>
                <div class="bg-[#D4A574] bg-opacity-10 rounded-lg p-6 border-2 border-[#D4A574]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-2xl font-bold text-gray-900">{{ $category->full_name }}</span>
                        <span class="px-4 py-2 bg-[#2C5F4F] text-white text-sm font-semibold rounded-full">{{ $category->match_type }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Slots:</span>
                            <span class="font-semibold text-gray-900 ml-2">{{ $category->approved_count ?? 0 }} / {{ $category->slots ?? 'Unlimited' }}</span>
                        </div>
                        @if($category->min_age || $category->max_age)
                        <div>
                            <span class="text-gray-600">Age Range:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                @if($category->min_age && $category->max_age)
                                    {{ $category->min_age }} - {{ $category->max_age }} years
                                @elseif($category->min_age)
                                    {{ $category->min_age }}+ years
                                @else
                                    Under {{ $category->max_age }} years
                                @endif
                            </span>
                        </div>
                        @endif
                        @if($category->skill_level && $category->skill_level !== 'Open')
                        <div>
                            <span class="text-gray-600">Skill Level:</span>
                            <span class="font-semibold text-gray-900 ml-2 capitalize">{{ $category->skill_level }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Partner Status (Doubles/Mixed) -->
            @php
                $name = strtolower($category->name);
                $matchType = strtolower($category->match_type ?? '');
                $type = strtolower($category->type ?? '');
                $isDoublesCategory = str_contains($name, 'doubles') || str_contains($name, 'mixed')
                    || str_contains($matchType, 'double') || str_contains($matchType, 'mixed')
                    || in_array($type, ['md', 'wd', 'xd', 'doubles', 'mixed'], true);
            @endphp
            @if($isDoublesCategory)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-[#2C5F4F] mb-3">Partner Status</h2>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-4">
                    @if($partnerCandidate)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Current Partner</p>
                                <p class="font-semibold text-gray-900">{{ $partnerCandidate->first_name }} {{ $partnerCandidate->last_name }}</p>
                                <p class="text-xs text-gray-600 mt-1">Partner linked for this category.</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Linked</span>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="rounded-lg border-l-4 border-yellow-500 bg-gradient-to-r from-yellow-50 to-white px-4 py-3 shadow-sm flex items-start gap-3">
                                <div class="mt-0.5 text-yellow-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86A2.07 2.07 0 0021 16.93L13.93 5.07a2.07 2.07 0 00-3.86 0L3 16.93A2.07 2.07 0 005.07 19z"/>
                                    </svg>
                                </div>
                                <div class="text-sm">
                                    <p class="font-semibold text-yellow-900">Partner required to complete registration</p>
                                    <p class="text-gray-700">Please link an accepted partner before submitting for this doubles/mixed category.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('player.invitations.search', [$tournament->id, $category->id]) }}" class="px-4 py-2 bg-[#2C5F4F] text-white rounded-lg hover:bg-[#244f41] text-sm">Invite/Search Partner</a>
                                <a href="{{ route('player.invitations.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm">View Invitations</a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1">Pending Invitations (Sent)</p>
                            <div class="space-y-1">
                                @forelse(($sentInvitations ?? collect([]))->where('status', 'pending') as $invite)
                                    <p class="text-sm text-gray-800">To: {{ $invite->invitee->first_name ?? '' }} {{ $invite->invitee->last_name ?? '' }}</p>
                                @empty
                                    <p class="text-xs text-gray-500">None</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-1">Pending Invitations (Received)</p>
                            <div class="space-y-1">
                                @forelse(($receivedInvitations ?? collect([]))->where('status', 'pending') as $invite)
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-800">From: {{ $invite->inviter->first_name ?? '' }} {{ $invite->inviter->last_name ?? '' }}</p>
                                        <div class="flex gap-2">
                                            <form action="{{ route('player.invitations.accept', $invite->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 text-xs bg-[#2C5F4F] text-white rounded hover:bg-[#244f41]">Accept</button>
                                            </form>
                                            <form action="{{ route('player.invitations.reject', $invite->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">None</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Eligibility Check -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-[#2C5F4F] mb-4">Eligibility Check</h2>
                
                @if($eligibility['eligible'])
                <div class="bg-green-50 border-l-4 border-green-500 p-6 mb-6">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-lg font-semibold text-green-800">You are eligible to register!</span>
                    </div>
                </div>
                @else
                <div class="bg-red-50 border-l-4 border-red-500 p-6 mb-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-lg font-semibold text-red-800 mb-3">You are not eligible to register</p>
                            @if(!empty($eligibility['reasons']))
                            <ul class="list-disc list-inside text-red-700 space-y-2">
                                @foreach($eligibility['reasons'] as $reason)
                                <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Eligibility Details -->
                <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Age Requirement</span>
                        <span class="text-gray-900">
                            @if($category->min_age && $category->max_age)
                                {{ $category->min_age }} - {{ $category->max_age }} years
                            @elseif($category->min_age)
                                {{ $category->min_age }}+ years
                            @elseif($category->max_age)
                                Under {{ $category->max_age }} years
                            @else
                                <span class="text-green-600 font-semibold">Open to all ages</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Skill Level</span>
                        <span class="text-gray-900 capitalize">
                            @php
                                $skill = $category->skill_level;
                            @endphp
                            @if(empty($skill) || strtolower($skill) === 'all' || strtolower($skill) === 'open')
                                All Levels
                            @else
                                {{ $skill }}
                            @endif
                        </span>
                    </div>
                    @if($eloRequirement)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Required ELO Range</span>
                        <span class="text-gray-900">
                            @if($eloRequirement['min'] !== null && $eloRequirement['max'] !== null)
                                {{ $eloRequirement['min'] }} - {{ $eloRequirement['max'] }}
                            @elseif($eloRequirement['min'] !== null)
                                {{ $eloRequirement['min'] }}+
                            @elseif($eloRequirement['max'] !== null)
                                Up to {{ $eloRequirement['max'] }}
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Your Current ELO</span>
                        <span class="text-gray-900 font-semibold">{{ number_format($playerElo, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">ELO Status</span>
                        <span class="font-semibold {{ ($eloRequirement['min'] !== null && $playerElo < $eloRequirement['min']) || ($eloRequirement['max'] !== null && $playerElo > $eloRequirement['max']) ? 'text-red-600' : 'text-green-600' }}">
                            @if(($eloRequirement['min'] !== null && $playerElo < $eloRequirement['min']))
                                Not eligible - ELO too low
                            @elseif($eloRequirement['max'] !== null && $playerElo > $eloRequirement['max'])
                                Not eligible - ELO too high
                            @else
                                Eligible
                            @endif
                        </span>
                    </div>
                    @endif
                    @if(str_contains(strtolower($category->name), 'men') || str_contains(strtolower($category->name), 'women') || str_contains(strtolower($category->name), 'mixed'))
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Gender Requirement</span>
                        <span class="text-gray-900">
                            @if(str_contains(strtolower($category->name), 'men') && !str_contains(strtolower($category->name), 'women') && !str_contains(strtolower($category->name), 'mixed'))
                                Men Only
                            @elseif(str_contains(strtolower($category->name), 'women') && !str_contains(strtolower($category->name), 'mixed'))
                                Women Only
                            @elseif(str_contains(strtolower($category->name), 'mixed'))
                                Mixed (One Male, One Female)
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-semibold text-[#2C5F4F] mb-4">Tournament Fee</h2>
                <div class="bg-[#C85A54] bg-opacity-10 rounded-lg p-6 border border-[#C85A54]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-lg font-medium text-gray-700">Tournament Fee</span>
                        <span class="text-3xl font-bold text-[#C85A54]">₱{{ number_format($tournament->tournament_fee, 2) }}</span>
                    </div>
                    <p class="text-sm text-gray-600">Payment is handled outside the system. Please contact the club manager for payment details.</p>
                </div>
            </div>

            <!-- Registration Form -->
            @if($eligibility['eligible'])
                {{-- Partner required notice already shown above; no duplicate prompt here --}}
            <form action="{{ route('player.tournaments.register') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                @if($isDoublesCategory && isset($partnerCandidate))
                    <input type="hidden" name="partner_id" value="{{ $partnerCandidate->id }}">
                @endif
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <input type="checkbox" id="confirm-registration" name="confirm" value="1" required class="mt-1 h-5 w-5 text-[#2C5F4F] border-gray-300 rounded focus:ring-[#2C5F4F]">
                        <label for="confirm-registration" class="ml-3 text-gray-700">
                            <span class="font-semibold text-lg">I confirm that I want to register for this tournament</span>
                            <p class="text-sm text-gray-600 mt-2">By checking this box, you agree to participate in this tournament and pay the registration fee (₱{{ number_format($tournament->tournament_fee, 2) }}) upon approval.</p>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-[#C85A54] text-white font-semibold rounded-lg hover:bg-[#B54A44] transition flex items-center {{ ($isDoublesCategory && !isset($partnerCandidate)) ? 'opacity-60 cursor-not-allowed bg-gray-300 text-gray-600 hover:bg-gray-300' : '' }}"
                        @if($isDoublesCategory && !isset($partnerCandidate)) disabled @endif>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm Registration
                    </button>
                </div>
            </form>
            @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded">
                <p class="text-yellow-800 font-semibold">You cannot proceed with registration because you do not meet the eligibility requirements. Please review the eligibility requirements above.</p>
            </div>
            <div class="mt-6 flex justify-end">
                <a href="{{ route('player.tournaments.show', $tournament->id) }}" class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                    Back to Tournament
                </a>
            </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>

