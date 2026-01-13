<?php

namespace App\Services;

use App\Models\User;
use App\Models\Club;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Models\EloRating;
use App\Models\RankingHistory;
use App\Models\TournamentCategory;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use App\Enums\CategoryType;
use Illuminate\Support\Facades\Cache;

class StatisticsService
{
    public function getPlayerStatistics(User $player): array
    {
        $cacheKey = "player_stats_{$player->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($player) {
            $matches = TournamentMatch::where(function($query) use ($player) {
                $query->where('player1_id', $player->id)
                      ->orWhere('player2_id', $player->id)
                      ->orWhere('player1_partner_id', $player->id)
                      ->orWhere('player2_partner_id', $player->id);
            })->with(['result', 'tournament', 'category'])->get();

            $wins = 0;
            $losses = 0;

            foreach ($matches as $match) {
                if (!$match->result || !$match->winner_id) {
                    continue;
                }

                $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                $playerWon = false;
                
                if ($isDoubles && $match->winner_partner_id) {
                    $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
                } else {
                    $playerWon = ($match->winner_id === $player->id);
                }

                if ($playerWon) {
                    $wins++;
                } else {
                    $playerParticipated = (
                        $match->player1_id === $player->id ||
                        $match->player2_id === $player->id ||
                        $match->player1_partner_id === $player->id ||
                        $match->player2_partner_id === $player->id
                    );
                    
                    if ($playerParticipated) {
                        $losses++;
                    }
                }
            }

            $totalMatches = $wins + $losses;
            $winRate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;

            $categoryStats = $this->calculateCategoryStatistics($matches, $player);

            $recentMatches = $matches->filter(function($match) {
                return $match->status === MatchStatus::COMPLETED->value && $match->result;
            })->sortByDesc('updated_at')->take(10);

            $recentWins = 0;
            $recentLosses = 0;
            foreach ($recentMatches as $match) {
                if (!$match->result || !$match->winner_id) continue;

                $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                $playerWon = false;
                if ($isDoubles && $match->winner_partner_id) {
                    $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
                } else {
                    $playerWon = ($match->winner_id === $player->id);
                }

                if ($playerWon) {
                    $recentWins++;
                } else {
                    $recentLosses++;
                }
            }
            $recentWinRate = ($recentWins + $recentLosses) > 0 ? round(($recentWins / ($recentWins + $recentLosses)) * 100, 1) : 0;

            $averagePoints = $this->calculateAveragePoints($matches, $player);
            $tournamentStats = $this->calculateTournamentStatistics($player);

            $gender = $player->gender ?? 'Male';
            $categoriesToLoad = $gender === 'Female' 
                ? [CategoryType::WOMENS_SINGLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value]
                : [CategoryType::MENS_SINGLES->value, CategoryType::MENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value];
            
            $eloRatings = EloRating::where('player_id', $player->id)
                ->whereIn('category', $categoriesToLoad)
                ->get();

            $rankingHistory = $this->getRankingHistoryWithRanks($player);

            return [
                'total_matches' => $totalMatches,
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $winRate,
                'category_stats' => $categoryStats,
                'recent_win_rate' => $recentWinRate,
                'recent_wins' => $recentWins,
                'recent_losses' => $recentLosses,
                'average_points' => $averagePoints,
                'tournament_stats' => $tournamentStats,
                'elo_ratings' => $eloRatings,
                'ranking_history' => $rankingHistory,
            ];
        });
    }

    public function getClubStatistics(Club $club): array
    {
        $cacheKey = "club_stats_{$club->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($club) {
            $members = $club->clubPlayers()->where('status', 'approved')->get();
            $totalMembers = $members->count();
            $activeMembers = $totalMembers;

            $tournaments = $club->tournaments()->get();
            $tournamentsHosted = $tournaments->count();
            $tournamentsByStatus = [
                'published' => $tournaments->where('status', TournamentStatus::PUBLISHED->value)->count(),
                'upcoming' => $tournaments->where('status', TournamentStatus::UPCOMING->value)->count(),
                'ongoing' => $tournaments->where('status', TournamentStatus::ONGOING->value)->count(),
                'completed' => $tournaments->where('status', TournamentStatus::COMPLETED->value)->count(),
            ];

            $totalMatches = TournamentMatch::whereIn('tournament_id', $tournaments->pluck('id'))->count();
            $completedMatches = TournamentMatch::whereIn('tournament_id', $tournaments->pluck('id'))
                ->where('status', MatchStatus::COMPLETED->value)
                ->count();

            $memberEloRatings = EloRating::whereIn('player_id', $members->pluck('player_id'))
                ->get();
            $averageElo = $memberEloRatings->count() > 0 
                ? round($memberEloRatings->avg('current_rating'), 0) 
                : 0;

            return [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'tournaments_hosted' => $tournamentsHosted,
                'tournaments_by_status' => $tournamentsByStatus,
                'total_matches' => $totalMatches,
                'completed_matches' => $completedMatches,
                'average_elo' => $averageElo,
            ];
        });
    }

    public function getManagerStatistics(User $manager): array
    {
        $cacheKey = "manager_stats_{$manager->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($manager) {
            $club = $manager->managedClub;
            if (!$club) {
                return [
                    'tournaments_organized' => 0,
                    'total_participants' => 0,
                    'matches_managed' => 0,
                    'average_tournament_size' => 0,
                    'completion_rate' => 0,
                ];
            }

            $tournaments = $club->tournaments()->where('organizer_id', $manager->id)->get();
            $tournamentsOrganized = $tournaments->count();

            $totalParticipants = TournamentRegistration::whereIn('tournament_id', $tournaments->pluck('id'))
                ->distinct('player_id')
                ->count();

            $matchesManaged = TournamentMatch::whereIn('tournament_id', $tournaments->pluck('id'))->count();

            $averageTournamentSize = $tournamentsOrganized > 0 
                ? round($totalParticipants / $tournamentsOrganized, 1) 
                : 0;

            $completedTournaments = $tournaments->where('status', TournamentStatus::COMPLETED->value)->count();
            $completionRate = $tournamentsOrganized > 0 
                ? round(($completedTournaments / $tournamentsOrganized) * 100, 1) 
                : 0;

            return [
                'tournaments_organized' => $tournamentsOrganized,
                'total_participants' => $totalParticipants,
                'matches_managed' => $matchesManaged,
                'average_tournament_size' => $averageTournamentSize,
                'completion_rate' => $completionRate,
            ];
        });
    }

    private function calculateCategoryStatistics($matches, User $player): array
    {
        $categoryStats = [];
        foreach (CategoryType::cases() as $categoryType) {
            $categoryStats[$categoryType->value] = [
                'wins' => 0,
                'losses' => 0,
                'matches' => 0,
                'win_rate' => 0,
            ];
        }

        foreach ($matches as $match) {
            if (!$match->result || !$match->winner_id || !$match->category) {
                continue;
            }

            $categoryType = $match->category->type ?? CategoryType::MENS_SINGLES->value;
            if (!isset($categoryStats[$categoryType])) {
                continue;
            }

            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                $playerWon = ($match->winner_id === $player->id);
            }

            $categoryStats[$categoryType]['matches']++;
            if ($playerWon) {
                $categoryStats[$categoryType]['wins']++;
            } else {
                $categoryStats[$categoryType]['losses']++;
            }
        }

        foreach ($categoryStats as $category => &$stats) {
            if ($stats['matches'] > 0) {
                $stats['win_rate'] = round(($stats['wins'] / $stats['matches']) * 100, 1);
            }
        }

        return $categoryStats;
    }

    private function calculateAveragePoints($matches, User $player): float
    {
        $totalPoints = 0;
        $totalSets = 0;

        foreach ($matches as $match) {
            if (!$match->result) {
                continue;
            }

            $result = $match->result;
            $isPlayer1 = ($match->player1_id === $player->id || $match->player1_partner_id === $player->id);

            if ($result->player1_set1_score !== null && $result->player2_set1_score !== null) {
                $totalPoints += $isPlayer1 ? $result->player1_set1_score : $result->player2_set1_score;
                $totalSets++;
            }
            if ($result->player1_set2_score !== null && $result->player2_set2_score !== null) {
                $totalPoints += $isPlayer1 ? $result->player1_set2_score : $result->player2_set2_score;
                $totalSets++;
            }
            if ($result->player1_set3_score !== null && $result->player2_set3_score !== null) {
                $totalPoints += $isPlayer1 ? $result->player1_set3_score : $result->player2_set3_score;
                $totalSets++;
            }
        }

        return $totalSets > 0 ? round($totalPoints / $totalSets, 1) : 0;
    }

    private function calculateTournamentStatistics(User $player): array
    {
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->get();

        $tournamentsJoined = $registrations->pluck('tournament_id')->unique()->count();

        $completedTournaments = $registrations->filter(function($reg) {
            return $reg->tournament && $reg->tournament->status === TournamentStatus::COMPLETED->value;
        })->pluck('tournament_id')->unique()->count();

        $bestFinish = null;
        $roundPriority = [
            'Finals' => 1,
            'Semifinals' => 2,
            'Quarterfinals' => 3,
            'Round of 16' => 4,
            'Round of 32' => 5,
            'Round of 64' => 6,
            'Second Round' => 7,
            'First Round' => 8,
        ];

        $playerMatches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })
        ->where('status', MatchStatus::COMPLETED->value)
        ->whereNotNull('round')
        ->with(['tournament', 'category'])
        ->get();

        foreach ($playerMatches as $match) {
            if (!$match->tournament || $match->tournament->status !== TournamentStatus::COMPLETED->value) {
                continue;
            }

            $maxParticipants = TournamentCategory::where('tournament_id', $match->tournament->id)
                ->max('max_participants') ?? 32;
            $maxRounds = $match->tournament->bracket_type === 'round_robin' 
                ? $maxParticipants - 1 
                : (int)ceil(log($maxParticipants, 2));

            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName(
                $match->tournament->bracket_type ?? 'single_elimination',
                (int)$match->round,
                $maxRounds
            );

            $normalizedRoundName = $roundName;
            if (preg_match('/^Round (\d+)$/', $roundName, $matches)) {
                $roundNum = (int)$matches[1];
                $normalizedRoundName = $roundNum <= 2 ? "Round {$roundNum}" : $roundName;
            }

            if (!$bestFinish) {
                $bestFinish = $normalizedRoundName;
            } else {
                $currentPriority = $roundPriority[$normalizedRoundName] ?? 999;
                $bestPriority = $roundPriority[$bestFinish] ?? 999;
                if ($currentPriority < $bestPriority) {
                    $bestFinish = $normalizedRoundName;
                }
            }
        }

        return [
            'tournaments_joined' => $tournamentsJoined,
            'tournaments_completed' => $completedTournaments,
            'best_finish' => $bestFinish ?? 'N/A',
        ];
    }

    private function getRankingHistoryWithRanks(User $player)
    {
        $rankingHistory = RankingHistory::where('player_id', $player->id)
            ->whereNot(function($query) {
                $query->whereNull('tournament_id')
                      ->where('change', '=', 0);
            })
            ->with('tournament')
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();

        return $rankingHistory->map(function ($history) {
            $allHistoryForCategory = RankingHistory::where('category', $history->category)
                ->where('recorded_at', '<=', $history->recorded_at)
                ->orderBy('recorded_at', 'desc')
                ->get()
                ->groupBy('player_id')
                ->map(function ($playerHistories) {
                    $first = $playerHistories->first();
                    return $first ? $first->rating : 0;
                })
                ->filter(function($rating) {
                    return $rating > 0;
                })
                ->sortDesc()
                ->values();

            $playersWithHigherRating = $allHistoryForCategory->filter(function ($rating) use ($history) {
                return $rating > $history->rating;
            })->count();

            $rank = $playersWithHigherRating + 1;

            if ($allHistoryForCategory->count() > 0) {
                $history->calculated_rank = $rank;
            } else {
                $history->calculated_rank = null;
            }

            return $history;
        });
    }

    public function invalidatePlayerCache(User $player): void
    {
        Cache::forget("player_stats_{$player->id}");
    }

    public function invalidateClubCache(Club $club): void
    {
        Cache::forget("club_stats_{$club->id}");
    }

    public function invalidateManagerCache(User $manager): void
    {
        Cache::forget("manager_stats_{$manager->id}");
    }
}

