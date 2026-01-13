<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-8">
                <div class="max-w-6xl mx-auto" x-data="{ activeTab: 'overview' }">
                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('manager.players') }}" class="inline-flex items-center gap-2 text-[#2C5F4F] hover:text-[#244D3E] font-semibold transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Players List
                        </a>
                    </div>

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
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-1 px-4" aria-label="Tabs">
                                <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                                    Overview
                                </button>
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
                            </nav>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
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
                                            <p class="text-sm text-gray-600 mb-1">{{ $categoryName }}</p>
                                            <p class="text-2xl font-bold text-[#C85A54]">{{ number_format($rating->current_rating) }}</p>
                                            <p class="text-xs text-gray-500">Peak: {{ number_format($rating->peak_rating) }}</p>
                                        </div>
                                    @empty
                                        <div class="col-span-2 text-center text-gray-500 py-4">
                                            <p>No ELO ratings yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Matches Tab -->
                        <div x-show="activeTab === 'matches'" x-cloak>
                            <h3 class="text-lg font-bold mb-4">MATCH HISTORY</h3>
                            <div class="bg-white border-2 border-[#D4A574] rounded-lg overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tournament</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opponent</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($recentMatches->sortByDesc(function($match) { return $match->updated_at ?? $match->created_at; }) as $match)
                                            @php
                                                $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                                                $opponent = null;
                                                $opponentPartner = null;
                                                
                                                if ($match->player1_id === $player->id || $match->player1_partner_id === $player->id) {
                                                    $opponent = $match->player2 ?? null;
                                                    $opponentPartner = $match->player2Partner ?? null;
                                                } else {
                                                    $opponent = $match->player1 ?? null;
                                                    $opponentPartner = $match->player1Partner ?? null;
                                                }
                                                
                                                $isWinner = false;
                                                $score = 'N/A';
                                                
                                                if ($match->result) {
                                                    $isWinner = $match->result->winner_id === $player->id || 
                                                               ($match->winner_partner_id && $match->winner_partner_id === $player->id);
                                                    
                                                    $matchScoreService = app(\App\Services\MatchScoreService::class);
                                                    $finalScore = $matchScoreService->calculateFinalScore($match->result);
                                                    $setScores = $matchScoreService->getFormattedSetScores($match->result);
                                                    $score = 'Final: ' . $finalScore['final_score'];
                                                    foreach ($setScores as $set => $setScore) {
                                                        $score .= ' | ' . ucfirst(str_replace('set', 'Set ', $set)) . ': ' . $setScore;
                                                    }
                                                }
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $match->tournament?->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $match->category?->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if($opponent)
                                                            {{ $opponent->first_name }} {{ $opponent->last_name }}
                                                            @if($isDoubles && $opponentPartner)
                                                                / {{ $opponentPartner->first_name }} {{ $opponentPartner->last_name }}
                                                            @endif
                                                        @else
                                                            TBD
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">
                                                        @if($match->scheduled_date)
                                                            {{ \Carbon\Carbon::parse($match->scheduled_date)->format('M d, Y') }}
                                                            @if($match->scheduled_time)
                                                                {{ \Carbon\Carbon::parse($match->scheduled_time)->format('H:i') }}
                                                            @endif
                                                        @else
                                                            {{ ($match->updated_at ?? $match->created_at)->format('M d, Y') }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($match->result)
                                                        @php
                                                            $matchScoreService = app(\App\Services\MatchScoreService::class);
                                                            $finalScore = $matchScoreService->calculateFinalScore($match->result);
                                                            $setScores = $matchScoreService->getFormattedSetScores($match->result);
                                                        @endphp
                                                        <div class="text-sm space-y-1">
                                                            <div class="font-bold text-gray-900">Final: {{ $finalScore['final_score'] }}</div>
                                                            <div class="text-xs text-gray-600 space-y-0.5">
                                                                @foreach($setScores as $set => $setScore)
                                                                    <div>{{ ucfirst(str_replace('set', 'Set ', $set)) }}: {{ $setScore }}</div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="text-sm text-gray-500">{{ $score }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($match->result)
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $isWinner ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $isWinner ? 'WON' : 'LOST' }}
                                                        </span>
                                                    @else
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                            PENDING
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                                    <p>No match history yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tournaments Tab -->
                        <div x-show="activeTab === 'tournaments'" x-cloak>
                            <h3 class="text-lg font-bold mb-4">TOURNAMENT HISTORY</h3>
                            <div class="bg-white border-2 border-[#D4A574] rounded-lg overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tournament</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($registrations as $registration)
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-gray-100 text-gray-800',
                                                    'eligible' => 'bg-blue-100 text-blue-800',
                                                    'awaiting_payment' => 'bg-yellow-100 text-yellow-800',
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'withdrawn' => 'bg-gray-100 text-gray-800',
                                                ];
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $registration->tournament?->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $registration->category?->full_name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $registration->tournament?->start_date?->format('M d, Y') ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$registration->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                        {{ strtoupper(str_replace('_', ' ', $registration->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ $registration->tournament ? route('manager.tournaments.show', $registration->tournament) : '#' }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                                        View Tournament
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                                    <p>No tournament registrations yet.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Statistics Tab -->
                        <div x-show="activeTab === 'statistics'" x-cloak>
                            <h3 class="text-lg font-bold mb-4">PLAYER STATISTICS</h3>
                            
                            <!-- Recent Performance & Average Points -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">RECENT PERFORMANCE</p>
                                    <p class="text-xl font-bold text-[#2C5F4F]">{{ $recentWinRate }}%</p>
                                    <p class="text-xs text-gray-500 mt-1">Last 10 matches ({{ $recentWins }}W-{{ $recentLosses }}L)</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">AVG POINTS PER SET</p>
                                    <p class="text-xl font-bold text-[#C85A54]">{{ $averagePoints }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Across all matches</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">BEST FINISH</p>
                                    <p class="text-xl font-bold text-[#2C5F4F]">{{ $tournamentStats['best_finish'] ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Tournament record</p>
                                </div>
                            </div>

                            <!-- Tournament Participation -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">TOURNAMENTS JOINED</p>
                                    <p class="text-2xl font-bold">{{ $tournamentStats['tournaments_joined'] ?? 0 }}</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">TOURNAMENTS COMPLETED</p>
                                    <p class="text-2xl font-bold">{{ $tournamentStats['tournaments_completed'] ?? 0 }}</p>
                                </div>
                            </div>

                            <!-- Category-Specific Statistics -->
                            <div class="mb-6">
                                <h3 class="text-lg font-bold mb-3">PERFORMANCE BY CATEGORY</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    @php
                                        $categoryMap = [
                                            'MS' => "Men's Singles",
                                            'WS' => "Women's Singles",
                                            'MD' => "Men's Doubles",
                                            'WD' => "Women's Doubles",
                                            'XD' => "Mixed Doubles",
                                        ];
                                    @endphp
                                    @foreach($categoryStats as $category => $stats)
                                        @if($stats['matches'] > 0)
                                            <div class="border-2 border-[#D4A574] rounded-lg p-4">
                                                <p class="text-sm font-semibold text-gray-700 mb-2">{{ $categoryMap[$category] ?? $category }}</p>
                                                <div class="grid grid-cols-3 gap-2 text-center">
                                                    <div>
                                                        <p class="text-xs text-gray-600">Matches</p>
                                                        <p class="text-lg font-bold">{{ $stats['matches'] }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-600">Wins</p>
                                                        <p class="text-lg font-bold text-green-600">{{ $stats['wins'] }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-600">Win Rate</p>
                                                        <p class="text-lg font-bold text-[#2C5F4F]">{{ $stats['win_rate'] ?? 0 }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(collect($categoryStats)->sum('matches') === 0)
                                        <div class="col-span-2 text-center text-gray-500 py-4">
                                            <p>No category statistics yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Rankings Tab -->
                        <div x-show="activeTab === 'rankings'" x-cloak>
                            <h3 class="text-lg font-bold mb-4">RANKING HISTORY</h3>
                            <div class="bg-white border-2 border-[#D4A574] rounded-lg overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tournament</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($rankingHistory as $history)
                                            @php
                                                $categoryMap = [
                                                    'MS' => "Men's Singles",
                                                    'WS' => "Women's Singles",
                                                    'MD' => "Men's Doubles",
                                                    'WD' => "Women's Doubles",
                                                    'XD' => "Mixed Doubles",
                                                ];
                                                $categoryName = $categoryMap[$history->category] ?? strtoupper($history->category);
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $categoryName }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $history->tournament?->name ?? 'Initial Rating' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ $history->recorded_at->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-2xl font-bold text-[#C85A54]">{{ number_format($history->rating) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-600">#{{ $history->calculated_rank ?? $history->rank ?? 'N/A' }}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                                    <p>No ranking history available.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

