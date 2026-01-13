<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use App\Helpers\TournamentRoundHelper;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tournamentId = $request->get('tournament_id');
        $categoryId = $request->get('category_id');
        $sort = $request->get('sort', 'date');
        $dir  = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Get tournaments the player is registered in (as primary or partner) AND that are ongoing or completed
        $registrationTournamentIds = TournamentRegistration::where(function ($q) use ($user) {
                $q->where('player_id', $user->id)->orWhere('partner_id', $user->id);
            })
            ->whereHas('tournament', function ($q) {
                $q->whereIn('status', [TournamentStatus::ONGOING->value, TournamentStatus::COMPLETED->value]);
            })
            ->pluck('tournament_id');

        // Also include tournaments where the player appears in matches (ongoing or completed tournaments)
        $matchTournamentIds = TournamentMatch::where(function ($q) use ($user) {
                $q->where('player1_id', $user->id)
                  ->orWhere('player2_id', $user->id)
                  ->orWhere('player1_partner_id', $user->id)
                  ->orWhere('player2_partner_id', $user->id);
            })
            ->whereHas('tournament', function ($q) {
                $q->whereIn('status', [TournamentStatus::ONGOING->value, TournamentStatus::COMPLETED->value]);
            })
            ->pluck('tournament_id');

        $joinedTournamentIds = $registrationTournamentIds
            ->merge($matchTournamentIds)
            ->unique()
            ->values();

        $tournaments = Tournament::whereIn('id', $joinedTournamentIds)
            ->with('categories')
            ->orderBy('start_date', 'desc')
            ->get();

        // Build match query scoped to the player
        $matchesQuery = TournamentMatch::query()
            ->whereIn('tournament_id', $joinedTournamentIds)
            ->where(function ($q) use ($user) {
                $q->where('player1_id', $user->id)
                  ->orWhere('player2_id', $user->id)
                  ->orWhere('player1_partner_id', $user->id)
                  ->orWhere('player2_partner_id', $user->id);
            })
            ->with([
                'tournament',
                'category',
                'player1','player2',
                'player1Partner','player2Partner',
                'winner','winnerPartner',
                'result',
            ])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time', 'asc');

        if ($tournamentId) {
            $matchesQuery->where('tournament_id', $tournamentId);
        }

        if ($categoryId) {
            $matchesQuery->where('tournament_category_id', $categoryId);
        }

        $matchesQuery->where(function($q) {
            $q->whereIn('status', [MatchStatus::SCHEDULED->value, MatchStatus::ONGOING->value, MatchStatus::COMPLETED->value])
              ->orWhereHas('result', function($subQ) {
                  $subQ->whereNotNull('winner_id');
              });
        });

        $matches = $matchesQuery->get();
        
        foreach ($matches as $match) {
            if ($match->result && $match->result->winner_id && $match->status !== MatchStatus::COMPLETED->value) {
                $match->status = MatchStatus::COMPLETED->value;
            }
        }

        $roundSorter = function ($round, $tournament = null) {
            if ($tournament && $tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string)$round));
                if (preg_match('/round\s+(\d+)/i', $r, $m)) {
                    return (int)$m[1];
                }
                return 9999;
            }
            
            $r = strtolower(trim((string)$round));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 10 + (int)$m[1];
            if ($r === 'quarterfinals' || str_contains($r, 'quarter')) return 30;
            if ($r === 'semifinals' || str_contains($r, 'semi')) return 40;
            if ($r === 'finals' || str_contains($r, 'final')) return 50;
            if (preg_match('/round\s+(\d+)/i', $r, $m)) return 100 + (int)$m[1];
            if (is_numeric($round)) return (int)$round;
            return 999;
        };

        $roundLabel = function ($roundName, int $slots, int $participants, $tournament = null) {
            if ($tournament && $tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string) $roundName));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    return 'Round ' . (int)$m[1];
                }
                return ucfirst($roundName);
            }
            
            $basis = max(2, $participants ?: $slots);
            $progression = TournamentRoundHelper::getRoundProgressionForBracketSize($basis);
            
            $r = strtolower(trim((string) $roundName));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
            if (str_contains($r, 'quarter')) return 'Quarterfinals';
            if (str_contains($r, 'semi')) return 'Semifinals';
            if (str_contains($r, 'final')) return 'Finals';

            $idx = null;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                $idx = (int)$m[1];
            } elseif (is_numeric($roundName)) {
                $idx = (int)$roundName;
            }

            if ($idx !== null && !empty($progression)) {
                $pos = min(max(1, $idx), count($progression));
                return $progression[$pos - 1];
            }

            return ucfirst($roundName);
        };

        // Group by tournament and normalize labels
        $matchesByTournament = [];
        foreach ($matches as $m) {
            $tid = $m->tournament_id;
            if (!isset($matchesByTournament[$tid])) {
                $matchesByTournament[$tid] = [
                    'tournament' => $tournaments->firstWhere('id', $tid),
                    'matches' => [],
                ];
            }
            $catSlots = $m->category?->max_participants ?? $m->category?->slots ?? 0;
            $participants = 0;
            $tournament = $matchesByTournament[$tid]['tournament'] ?? null;
            $label = $roundLabel($m->round ?? 'Round 1', (int)$catSlots, (int)$participants, $tournament);
            $m->normalized_round = $label;
            $matchesByTournament[$tid]['matches'][] = $m;
        }

        // Sort matches inside each tournament
        foreach ($matchesByTournament as &$entry) {
            $tournament = $entry['tournament'] ?? null;
            $entry['matches'] = collect($entry['matches'])
                ->sort(function ($a, $b) use ($roundSorter, $sort, $dir, $tournament) {
                    $mult = $dir === 'desc' ? -1 : 1;
                    $aDate = ($a->scheduled_date ?? '') . ' ' . ($a->scheduled_time ?? '');
                    $bDate = ($b->scheduled_date ?? '') . ' ' . ($b->scheduled_time ?? '');

                    $aTeam = trim(($a->player1?->name ?? '') . ' ' . ($a->player1Partner?->name ? ' & ' . $a->player1Partner->name : ''));
                    $aTeam .= ' vs ' . trim(($a->player2?->name ?? '') . ' ' . ($a->player2Partner?->name ? ' & ' . $a->player2Partner->name : ''));
                    $bTeam = trim(($b->player1?->name ?? '') . ' ' . ($b->player1Partner?->name ? ' & ' . $b->player1Partner->name : ''));
                    $bTeam .= ' vs ' . trim(($b->player2?->name ?? '') . ' ' . ($b->player2Partner?->name ? ' & ' . $b->player2Partner->name : ''));

                    $aWinner = trim(($a->winner?->name ?? '') . ($a->winnerPartner?->name ? ' & ' . $a->winnerPartner->name : ''));
                    $bWinner = trim(($b->winner?->name ?? '') . ($b->winnerPartner?->name ? ' & ' . $b->winnerPartner->name : ''));

                    $map = [
                        'round'    => [$roundSorter($a->normalized_round ?? '', $tournament), $roundSorter($b->normalized_round ?? '', $tournament)],
                        'teams'    => [$aTeam, $bTeam],
                        'date'     => [$aDate, $bDate],
                        'court'    => [$a->court_number ?? '', $b->court_number ?? ''],
                        'status'   => [$a->status ?? '', $b->status ?? ''],
                        'winner'   => [$aWinner, $bWinner],
                    ];

                    $key = $map[$sort] ?? $map['date'];
                    if ($key[0] == $key[1]) {
                        return $mult * (($aDate <=> $bDate));
                    }
                    return $mult * (($key[0] <=> $key[1]));
                })
                ->values()
                ->all();
        }

        $selectedTournament = $tournamentId ? $tournaments->firstWhere('id', $tournamentId) : null;
        $categories = $selectedTournament ? $selectedTournament->categories : collect();

        return view('player.matches.index', [
            'matchesByTournament' => $matchesByTournament,
            'tournaments' => $tournaments,
            'selectedTournamentId' => $tournamentId,
            'selectedCategoryId' => $categoryId,
            'categories' => $categories,
            'sort' => $sort,
            'dir' => $dir,
            'user' => $user,
        ]);
    }
}
