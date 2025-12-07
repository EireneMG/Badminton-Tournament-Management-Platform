<x-dashboard-layout title="Player Profile">
    <div class="max-w-6xl mx-auto px-4 py-6" x-data="{ activeTab: 'overview' }">
        <!-- Back Button (only show when viewing other players) -->
        @if(auth()->check() && auth()->id() !== $player->id)
        <div class="mb-4">
            <a href="{{ route('players.index') }}" class="inline-flex items-center gap-2 text-[#2C5F4F] hover:text-[#244D3E] font-semibold transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Players List
            </a>
        </div>
        @endif

        <!-- Profile Header Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    @if($player->profile_photo)
                        <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->first_name }}" class="w-24 h-24 rounded-full object-cover border-4 border-[#D4A574] shadow-md">
                    @else
                        <div class="w-24 h-24 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white text-3xl font-bold border-4 border-[#D4A574] shadow-md">
                            {{ strtoupper(substr($player->first_name ?? 'U', 0, 1) . substr($player->last_name ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">{{ $player->first_name }} {{ $player->last_name }}</h1>
                        <p class="text-lg text-gray-600 mb-2">{{ $club?->name ?? 'No Club' }}</p>
                        @if($skillLevel)
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 text-sm font-semibold rounded-md" style="
                                    @if($skillLevel === 'A') background-color: #E9D5FF; color: #6B21A8;
                                    @elseif($skillLevel === 'B') background-color: #DBEAFE; color: #1E40AF;
                                    @elseif($skillLevel === 'C') background-color: #D1FAE5; color: #065F46;
                                    @else background-color: #F3F4F6; color: #374151;
                                    @endif
                                ">
                                    Level {{ $skillLevel }}
                                </span>
                                @if($primaryElo && $primaryElo->matches_played > 0)
                                    <span class="text-sm text-[#2C5F4F] font-semibold">(Official)</span>
                                @elseif($isProvisional)
                                    <span class="text-sm text-gray-500 italic">(Provisional)</span>
                                @endif
                                @if($primaryElo)
                                    <span class="text-sm text-gray-600">• ELO: <span class="font-bold text-[#C85A54]">{{ number_format($primaryElo->current_rating) }}</span></span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @if(auth()->check() && auth()->id() === $player->id)
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-[#2C5F4F] text-white rounded-lg hover:bg-[#234b3f] transition font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profile
                    </a>
                @endif
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-1 px-4" aria-label="Tabs">
                    <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                        Overview
                    </button>
                    @if(auth()->check() && auth()->id() === $player->id)
                        <button @click="activeTab = 'biodata'" :class="activeTab === 'biodata' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                            Biodata
                        </button>
                    @endif
                    <button @click="activeTab = 'matches'" :class="activeTab === 'matches' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                        Matches
                    </button>
                    <button @click="activeTab = 'tournaments'" :class="activeTab === 'tournaments' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                        Tournaments
                    </button>
                    <button @click="activeTab = 'rankings'" :class="activeTab === 'rankings' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                        Rankings
                    </button>
                    <button @click="activeTab = 'statistics'" :class="activeTab === 'statistics' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                        Statistics
                    </button>
                    @if(auth()->check() && auth()->id() === $player->id)
                        <button @click="activeTab = 'account'" :class="activeTab === 'account' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                            Account Settings
                        </button>
                    @endif
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" x-cloak>
                <!-- Player Stats Grid -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">AGE</p>
                        <p class="text-lg font-bold">{{ $age ?? 'N/A' }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">HEIGHT</p>
                        <p class="text-lg font-bold">{{ $player->height ? $player->height . ' cm' : 'N/A' }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">WEIGHT</p>
                        <p class="text-lg font-bold">{{ $player->weight ? $player->weight . ' kg' : 'N/A' }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">REGION</p>
                        <p class="text-lg font-bold">{{ $player->region ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Player Statistics -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">MATCHES PLAYED</p>
                        <p class="text-2xl font-bold">{{ $totalMatches }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">WINS</p>
                        <p class="text-2xl font-bold text-green-600">{{ $wins }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">LOSSES</p>
                        <p class="text-2xl font-bold text-red-600">{{ $losses }}</p>
                    </div>
                    <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-600 mb-1">WIN RATE</p>
                        <p class="text-2xl font-bold text-[#2C5F4F]">{{ $winRate }}%</p>
                    </div>
                </div>

                <!-- ELO Ratings by Category -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-3">ELO RATINGS</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @forelse($eloRatings as $rating)
                            @php
                                $categoryMap = [
                                    'MS' => "Men's Singles",
                                    'WS' => "Women's Singles",
                                    'MD' => "Men's Doubles",
                                    'WD' => "Women's Doubles",
                                    'XD' => "Mixed Doubles",
                                ];
                                $categoryName = $categoryMap[$rating->category] ?? strtoupper($rating->category);
                            @endphp
                            <div class="border-2 border-[#D4A574] rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">{{ $categoryName }}</p>
                                <p class="text-2xl font-bold text-[#C85A54]">{{ number_format($rating->current_rating) }}</p>
                                <p class="text-xs text-gray-500">Peak: {{ number_format($rating->peak_rating) }}</p>
                            </div>
                        @empty
                            <div class="col-span-2 text-center text-gray-500 py-4">
                                <p>No ELO ratings yet. Join tournaments to get rated!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Biodata Tab (Only visible to the player themselves) -->
            @if(auth()->check() && auth()->id() === $player->id)
            <div x-show="activeTab === 'biodata'" x-cloak>
                <div class="space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                        <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Personal Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Full Name</p>
                                <p class="font-semibold">{{ $player->first_name }} {{ $player->middle_name }} {{ $player->last_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Email</p>
                                <p class="font-semibold">{{ $player->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Contact Number</p>
                                <p class="font-semibold">{{ $player->contact_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Gender</p>
                                <p class="font-semibold">{{ ucfirst($player->gender ?? 'N/A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Date of Birth</p>
                                <p class="font-semibold">
                                    @if($player->birth_month && $player->birth_day && $player->birth_year)
                                        {{ \Carbon\Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day)->format('F d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Age</p>
                                <p class="font-semibold">{{ $age ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Location</p>
                                <p class="font-semibold">{{ $player->city ?? 'N/A' }}, {{ $player->province ?? 'N/A' }}, {{ $player->region ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Attributes -->
                    <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                        <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Physical Attributes</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Height</p>
                                <p class="font-semibold">{{ $player->height ? $player->height . ' cm' : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Weight</p>
                                <p class="font-semibold">{{ $player->weight ? $player->weight . ' kg' : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- School Information -->
                    @if($player->school_status || $player->school_name)
                    <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                        <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Education Background</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Education Status</p>
                                <p class="font-semibold">
                                    @php
                                        $educationStatusLabels = [
                                            'junior_high' => 'Junior High School Student',
                                            'senior_high' => 'Senior High School Student',
                                            'college_student' => 'College Student',
                                            'college_graduate' => 'College Graduate',
                                            'post_graduate' => 'Post-Graduate (Master\'s/Doctorate)',
                                            'not_applicable' => 'N/A (Did not attend school)',
                                            'student' => 'Currently a Student',
                                            'graduated' => 'Graduated',
                                        ];
                                        echo $educationStatusLabels[$player->school_status] ?? ucfirst(str_replace('_', ' ', $player->school_status ?? 'N/A'));
                                    @endphp
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">School Name</p>
                                <p class="font-semibold">{{ $player->school_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Badminton Experience -->
                    @if($player->years_of_experience || $player->experience_level || $player->competitive_background || ($player->badminton_history && count($player->badminton_history) > 0))
                    <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                        <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Badminton Experience</h3>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Years of Experience</p>
                                <p class="font-semibold">
                                    @php
                                        $yearsLabels = [
                                            'less_than_1' => 'Less than 1 year',
                                            '1_2' => '1–2 years',
                                            '3_5' => '3–5 years',
                                            '6_10' => '6–10 years',
                                            'more_than_10' => 'More than 10 years',
                                        ];
                                        echo $yearsLabels[$player->years_of_experience] ?? ($player->years_of_experience ?? 'N/A');
                                    @endphp
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Experience Level</p>
                                <p class="font-semibold">
                                    @php
                                        $experienceLevelLabels = [
                                            'beginner' => 'Beginner',
                                            'lower_intermediate' => 'Lower Intermediate',
                                            'intermediate' => 'Intermediate',
                                            'upper_intermediate' => 'Upper Intermediate',
                                            'advanced' => 'Advanced',
                                        ];
                                        echo $experienceLevelLabels[$player->experience_level] ?? ($player->experience_level ?? 'N/A');
                                    @endphp
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600 mb-1">Competitive Background</p>
                                <p class="font-semibold">
                                    @php
                                        $competitiveLabels = [
                                            'school_competitions' => 'School competitions',
                                            'local_tournaments' => 'Local tournaments',
                                            'regional_tournaments' => 'Regional tournaments',
                                            'national_tournaments' => 'National tournaments',
                                            'none' => 'None',
                                        ];
                                        echo $competitiveLabels[$player->competitive_background] ?? ($player->competitive_background ?? 'N/A');
                                    @endphp
                                </p>
                            </div>
                        </div>
                        @if($player->badminton_history && count($player->badminton_history) > 0)
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Additional Background:</p>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $historyLabels = [
                                        'tournament' => 'Tournament Experience',
                                        'school_event' => 'School Event',
                                        'community_event' => 'Community Event',
                                        'club_member' => 'Club Member',
                                        'recreational' => 'Recreational',
                                        'coached' => 'Coached',
                                        'varsity' => 'Varsity Player',
                                    ];
                                @endphp
                                @foreach($player->badminton_history as $history)
                                    <span class="px-3 py-1 bg-[#2C5F4F] text-white rounded-full text-sm font-medium">
                                        {{ $historyLabels[$history] ?? ucfirst($history) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Profile Photo -->
                    @if($player->profile_photo)
                    <div class="bg-white rounded-lg p-6 border-2 border-[#D4A574]">
                        <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">Profile Photo</h3>
                        <div class="flex justify-center">
                            <img src="{{ Storage::url($player->profile_photo) }}" alt="Profile Photo" class="w-48 h-48 rounded-lg object-cover border-2 border-[#D4A574] shadow-lg">
                        </div>
                    </div>
                    @endif
        </div>
    </div>
</x-dashboard-layout>
