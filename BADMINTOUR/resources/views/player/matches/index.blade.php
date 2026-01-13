<x-dashboard-layout title="My Matches">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">My Matches</h1>

        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg border border-gray-200">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tournament</label>
                <select name="tournament_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#2C5F4F]" onchange="this.form.submit()">
                    <option value="">Select Tournament</option>
                    @foreach($tournaments as $t)
                        <option value="{{ $t->id }}" {{ $selectedTournamentId == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-[#2C5F4F]" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($selectedTournamentId)
            @php
                $selectedTournament = $tournaments->firstWhere('id', $selectedTournamentId);
                $tournamentMatches = $matchesByTournament[$selectedTournamentId] ?? null;
            @endphp
            @if($tournamentMatches && isset($tournamentMatches['matches']) && count($tournamentMatches['matches']) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="text-lg font-semibold text-gray-900">{{ $tournamentMatches['tournament']->name ?? 'Tournament' }}</div>
                        <div class="text-sm text-gray-600">
                            {{ optional($tournamentMatches['tournament']->start_date)->format('M d, Y') }} - {{ optional($tournamentMatches['tournament']->end_date)->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                @php
                                    $nextDir = $dir === 'asc' ? 'desc' : 'asc';
                                    $sortLink = function($key) use ($nextDir, $selectedTournamentId, $selectedCategoryId, $sort) {
                                        return request()->fullUrlWithQuery([
                                            'sort' => $key,
                                            'dir' => ($sort === $key ? $nextDir : 'asc'),
                                            'tournament_id' => $selectedTournamentId,
                                            'category_id' => $selectedCategoryId,
                                        ]);
                                    };
                                    $arrow = function($key) use ($sort, $dir) {
                                        if ($sort !== $key) return '↕';
                                        return $dir === 'asc' ? '▲' : '▼';
                                    };
                                @endphp
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <a href="{{ $sortLink('round') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Round <span class="text-gray-400">{{ $arrow('round') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <a href="{{ $sortLink('teams') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Team A vs Team B <span class="text-gray-400">{{ $arrow('teams') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <a href="{{ $sortLink('date') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Date / Time <span class="text-gray-400">{{ $arrow('date') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <a href="{{ $sortLink('court') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Court <span class="text-gray-400">{{ $arrow('court') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <a href="{{ $sortLink('status') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Status <span class="text-gray-400">{{ $arrow('status') }}</span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <a href="{{ $sortLink('winner') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                                            Result / Winner <span class="text-gray-400">{{ $arrow('winner') }}</span>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($tournamentMatches['matches'] as $match)
                                    @php
                                        $p1Name = $match->player1?->name ?? '';
                                        $p1 = trim(($match->player1?->first_name ?? '') . ' ' . ($match->player1?->last_name ?? ''));
                                        if(empty($p1)) {
                                            $p1 = $p1Name ?: 'TBD';
                                        }
                                        $p1p = null;
                                        if($match->player1Partner) {
                                            $p1pName = $match->player1Partner->name ?? '';
                                            $p1p = trim(($match->player1Partner->first_name ?? '') . ' ' . ($match->player1Partner->last_name ?? ''));
                                            if(empty($p1p)) {
                                                $p1p = $p1pName;
                                            }
                                        }
                                        $p2Name = $match->player2?->name ?? '';
                                        $p2 = trim(($match->player2?->first_name ?? '') . ' ' . ($match->player2?->last_name ?? ''));
                                        if(empty($p2)) {
                                            $p2 = $p2Name ?: 'TBD';
                                        }
                                        $p2p = null;
                                        if($match->player2Partner) {
                                            $p2pName = $match->player2Partner->name ?? '';
                                            $p2p = trim(($match->player2Partner->first_name ?? '') . ' ' . ($match->player2Partner->last_name ?? ''));
                                            if(empty($p2p)) {
                                                $p2p = $p2pName;
                                            }
                                        }
                                        $teamA = $p1p ? ($p1 . ' & ' . $p1p) : $p1;
                                        $teamB = $p2p ? ($p2 . ' & ' . $p2p) : $p2;
                                        
                                        $round = $match->normalized_round ?? $match->round ?? 'Round 1';
                                        if ($match->category && $match->tournament) {
                                            $slots = $match->category?->max_participants ?? $match->category?->slots ?? 16;
                                            $maxRounds = ($match->tournament && $match->tournament->bracket_type === 'round_robin') 
                                                ? ($slots - 1) 
                                                : (int)ceil(log($slots, 2));
                                            if (is_numeric($round)) {
                                                $round = \App\Helpers\TournamentRoundHelper::getRoundName(
                                                    $match->tournament?->bracket_type ?? 'single_elimination',
                                                    (int)$round,
                                                    $maxRounds
                                                );
                                            }
                                        }
                                        $hasScore = $match->result !== null && $match->result->winner_id;
                                        
                                        $isPlayerInTeamA = ($match->player1_id == $user->id) || ($match->player1_partner_id == $user->id);
                                        $isPlayerInTeamB = ($match->player2_id == $user->id) || ($match->player2_partner_id == $user->id);
                                        $playerWon = false;
                                        if ($hasScore && $match->result && $match->result->winner_id) {
                                            $playerWon = ($isPlayerInTeamA && $match->result->winner_id == $match->player1_id) || 
                                                        ($isPlayerInTeamB && $match->result->winner_id == $match->player2_id);
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 {{ $playerWon ? 'bg-green-50' : ($hasScore && !$playerWon ? 'bg-red-50' : '') }}">
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            {{ $round }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div class="flex items-center gap-2">
                                                <span class="{{ $isPlayerInTeamA ? 'font-semibold text-[#2C5F4F]' : '' }}">{{ $teamA ?? 'TBD' }}</span>
                                                <span class="text-gray-500">vs</span>
                                                <span class="{{ $isPlayerInTeamB ? 'font-semibold text-[#2C5F4F]' : '' }}">{{ $teamB ?? 'TBD' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            {{ $match->actual_scheduled_date ? $match->actual_scheduled_date->format('M d, Y') : 'Date TBD' }}
                                            <div class="text-xs text-gray-500">
                                                {{ $match->scheduled_time ? \Carbon\Carbon::parse($match->scheduled_time)->format('h:i A') : 'Time TBD' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            Court {{ $match->court_number ?? 'TBD' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            @php
                                                $displayStatus = $match->status ?? 'scheduled';
                                                if ($hasScore && $match->result && $match->result->winner_id) {
                                                    $displayStatus = 'completed';
                                                }
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium border
                                                {{ $displayStatus === 'completed' ? 'bg-green-100 text-green-700 border-green-300' : 
                                                   ($displayStatus === 'ongoing' ? 'bg-blue-100 text-blue-700 border-blue-300' : 
                                                   'bg-gray-100 text-gray-700 border-gray-300') }}">
                                                {{ ucfirst($displayStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if($hasScore && $match->result && $match->result->winner_id)
                                                @php
                                                    $matchScoreService = app(\App\Services\MatchScoreService::class);
                                                    $finalScore = $matchScoreService->calculateFinalScore($match->result);
                                                    $setScores = $matchScoreService->getFormattedSetScores($match->result);
                                                    $winnerDisplay = trim(($match->winner?->first_name ?? '') . ' ' . ($match->winner?->last_name ?? ''));
                                                    if(empty($winnerDisplay)) {
                                                        $winnerDisplay = $match->winner?->name ?? 'TBD';
                                                    }
                                                    if($match->winner_partner_id && $match->winnerPartner) {
                                                        $winnerPartner = trim(($match->winnerPartner->first_name ?? '') . ' ' . ($match->winnerPartner->last_name ?? ''));
                                                        if(empty($winnerPartner)) {
                                                            $winnerPartner = $match->winnerPartner->name ?? '';
                                                        }
                                                        $winnerDisplay = trim($winnerDisplay . ' & ' . $winnerPartner);
                                                    }
                                                @endphp
                                                <div class="space-y-1">
                                                    <div class="font-semibold {{ $playerWon ? 'text-green-600' : 'text-red-600' }}">
                                                        Winner: {{ $winnerDisplay }}
                                                        @if($playerWon)
                                                            <span class="text-xs font-normal text-green-600">(You Won)</span>
                                                        @else
                                                            <span class="text-xs font-normal text-red-600">(You Lost)</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs font-bold text-gray-900">Final: {{ $finalScore['final_score'] }}</div>
                                                    <div class="text-xs text-gray-600 space-y-0.5">
                                                        @foreach($setScores as $set => $score)
                                                            <div>{{ ucfirst(str_replace('set', 'Set ', $set)) }}: {{ $score }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-500">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white border border-gray-200 rounded-xl p-6 text-center text-gray-600">
                    No matches found for this tournament.
                </div>
            @endif
        @else
            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center text-gray-600">
                Please select a tournament to view your matches.
            </div>
        @endif
    </div>
</div>
</x-dashboard-layout>
