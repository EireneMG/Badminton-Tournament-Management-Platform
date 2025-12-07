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
        </div>
    </div>
</x-dashboard-layout>
