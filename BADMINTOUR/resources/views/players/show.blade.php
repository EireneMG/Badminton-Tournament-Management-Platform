<x-dashboard-layout title="Player Profile">
    <div class="max-w-5xl mx-auto" x-data="{ activeTab: 'overview' }">
        <!-- Player Header -->
        <div class="bg-white rounded-lg p-6 mb-6 border-b-4 border-[#D4A574]">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    @if($player->profile_photo)
                        <img src="{{ Storage::url($player->profile_photo) }}" alt="{{ $player->first_name }}" class="w-20 h-20 rounded-full object-cover border-2 border-[#D4A574]">
                    @else
                        <div class="w-20 h-20 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white text-2xl font-bold border-2 border-[#D4A574]">
                            {{ strtoupper(substr($player->first_name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-sm text-gray-600 uppercase">{{ $player->first_name }}</h1>
                        <h2 class="text-3xl font-bold uppercase">{{ $player->last_name }}</h2>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold">{{ $club?->name ?? 'NO CLUB' }}</p>
                    @if(auth()->check() && auth()->id() === $player->id)
                        <a href="{{ route('profile.edit') }}" class="inline-block mt-2 text-sm px-4 py-1.5 bg-[#2C5F4F] text-white rounded-md hover:bg-[#234b3f] transition">
                            Edit Profile
                        </a>
                    @endif
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b-2 border-[#D4A574] mb-6">
                <div class="flex space-x-8">
                    <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-[#C85A54]' : 'border-transparent hover:text-[#C85A54]'" class="px-2 py-3 border-b-2 font-semibold text-gray-900">OVERVIEW</button>
                    @if(auth()->check() && auth()->id() === $player->id)
                        <button @click="activeTab = 'biodata'" :class="activeTab === 'biodata' ? 'border-[#C85A54]' : 'border-transparent hover:text-[#C85A54]'" class="px-2 py-3 border-b-2 font-semibold text-gray-600">BIODATA</button>
                    @endif
                    <button @click="activeTab = 'matches'" :class="activeTab === 'matches' ? 'border-[#C85A54]' : 'border-transparent hover:text-[#C85A54]'" class="px-2 py-3 border-b-2 font-semibold text-gray-600">MATCHES</button>
                    <button @click="activeTab = 'tournaments'" :class="activeTab === 'tournaments' ? 'border-[#C85A54]' : 'border-transparent hover:text-[#C85A54]'" class="px-2 py-3 border-b-2 font-semibold text-gray-600">TOURNAMENTS</button>
                    <button @click="activeTab = 'rankings'" :class="activeTab === 'rankings' ? 'border-[#C85A54]' : 'border-transparent hover:text-[#C85A54]'" class="px-2 py-3 border-b-2 font-semibold text-gray-600">RANKINGS</button>
                </div>
            </div>

            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'">
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
                            <div class="border-2 border-[#D4A574] rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">{{ strtoupper($rating->category) }}</p>
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

            <!-- ELO Ratings by Category -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-3">ELO RATINGS</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @forelse($eloRatings as $rating)
                            <div class="border-2 border-[#D4A574] rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">{{ strtoupper($rating->category) }}</p>
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
                    
        </div>
    </div>
</x-dashboard-layout>
