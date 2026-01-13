<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\TournamentCategory;
use App\Services\TournamentStatusService;
use App\Services\EligibilityService;
use App\Models\PartnerInvitation;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use App\Enums\CategoryType;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class TournamentController extends Controller
{
    protected $tournamentStatusService;

    public function __construct(TournamentStatusService $tournamentStatusService)
    {
        $this->tournamentStatusService = $tournamentStatusService;
    }

    public function index(): View
    {
        // Update tournament statuses before displaying
        $this->tournamentStatusService->updateTournamentStatuses();
        
        $player = auth()->user();
        $now = Carbon::now();

        $sort = request('sort', 'start_date');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sorter = function ($collection) use ($sort, $dir) {
            $mult = $dir === 'desc' ? -1 : 1;
            return $collection->sort(function ($a, $b) use ($sort, $mult) {
                $map = [
                    'name' => [$a->name ?? '', $b->name ?? ''],
                    'start_date' => [$a->start_date ?? '', $b->start_date ?? ''],
                    'registration_deadline' => [$a->registration_deadline ?? '', $b->registration_deadline ?? ''],
                    'venue' => [$a->venue_name ?? $a->venue ?? '', $b->venue_name ?? $b->venue ?? ''],
                    'organizer' => [$a->club->name ?? '', $b->club->name ?? ''],
                    'categories' => [
                        implode(', ', optional($a->categories)->pluck('name')->toArray() ?? []),
                        implode(', ', optional($b->categories)->pluck('name')->toArray() ?? [])
                    ],
                ];
                $key = $map[$sort] ?? $map['start_date'];
                if ($key[0] == $key[1]) {
                    return $mult * (($a->id ?? 0) <=> ($b->id ?? 0));
                }
                return $mult * (($key[0] <=> $key[1]));
            })->values();
        };
        
        $ongoingTournaments = Tournament::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereNotIn('status', [TournamentStatus::CANCELLED->value])
            ->with(['club', 'categories'])
            ->get();
        $ongoingTournaments = $sorter($ongoingTournaments);
        
        $upcomingTournaments = Tournament::where('start_date', '>', $now)
            ->where('registration_deadline', '>', $now)
            ->whereNotIn('status', [TournamentStatus::CANCELLED->value])
            ->with(['club', 'categories'])
            ->get();
        $upcomingTournaments = $sorter($upcomingTournaments);
        
        $completedTournaments = Tournament::where('end_date', '<', $now)
            ->whereNotIn('status', [TournamentStatus::CANCELLED->value])
            ->with(['club', 'categories'])
            ->get();
        $completedTournaments = $sorter($completedTournaments);
        
        $myTournaments = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament.club', 'category'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('tournament')
            ->unique('id');
        $myTournaments = $sorter($myTournaments);
        
        return view('tournaments.index', compact(
            'ongoingTournaments',
            'upcomingTournaments',
            'completedTournaments',
            'myTournaments',
            'sort',
            'dir'
        ));
    }
    
    public function show(Tournament $tournament): View
    {
        // Update tournament status if needed (upcoming → ongoing → completed)
        // This ensures status is always current when viewing
        $this->tournamentStatusService->updateTournamentStatuses($tournament);
        $tournament->refresh();
        
        $player = auth()->user();
        
        // Prevent players from viewing cancelled tournaments
        if ($tournament->status === 'cancelled') {
            abort(404, 'Tournament not found.');
        }
        
        // Identify seeded test tournaments to keep them isolated from real users
        $seededNames = [
            'Completed Open 1',
            'Completed Open 2',
            'Ongoing Test Tournament',
            'Upcoming Test Tournament',
        ];
        $isSeededTournament = in_array($tournament->name, $seededNames, true);
        $seededEmailPattern = 'player.test%';
        
        // Load categories with a fresh query to ensure no duplicates
        $tournament->load('club.manager');
        
        // Load categories separately to ensure uniqueness - use groupBy to prevent duplicates
        $categories = TournamentCategory::where('tournament_id', $tournament->id)
            ->withCount(['registrations as approved_count' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->with(['registrations' => function ($q) {
                $q->where('status', 'approved')
                  ->with(['player', 'partner']);
            }])
            ->orderBy('id')
            ->get()
            ->unique('id')
            ->values();
        
        // Set the categories relationship
        $tournament->setRelation('categories', $categories->unique('name')->values());
        
        // Get all approved registrations grouped by category for player registrations display
        // Exclude withdrawn and withdrawal_requested registrations
        $registrationsByCategory = [];
        foreach ($tournament->categories as $category) {
            $registrationsByCategory[$category->id] = $category->registrations
                ->filter(function($reg) use ($isSeededTournament, $seededEmailPattern) {
                    if ($reg->status !== 'approved') {
                        return false;
                    }
                    if ($isSeededTournament) {
                        $playerOk = $reg->player && str_contains($reg->player->email, 'player.test');
                        $partnerOk = !$reg->partner || str_contains($reg->partner->email, 'player.test');
                        return $playerOk && $partnerOk;
                    }
                    return true;
                })
                ->values();
        }
        
        // Participant counts per category for round normalization
        $participantCounts = [];
        foreach ($registrationsByCategory as $catId => $regs) {
            $participantCounts[$catId] = $regs->count();
        }

        // Load matches for completed tournaments
        $matchesByCategory = [];
        if ($tournament->status === TournamentStatus::COMPLETED->value || $tournament->status === TournamentStatus::ONGOING->value) {
            $tournament->load([
                'matches.player1',
                'matches.player2',
                'matches.player1Partner',
                'matches.player2Partner',
                'matches.winner',
                'matches.winnerPartner',
                'matches.category',
                'matches.result'
            ]);
            
            // Group matches by category and normalized round
            $roundSorter = function ($round) use ($tournament) {
                if ($tournament->bracket_type === 'round_robin') {
                    $r = strtolower(trim((string)$round));
                    if (preg_match('/round\\s+(\\d+)/i', $r, $m)) {
                        return (int)$m[1];
                    }
                    return 9999;
                }
                
                $r = strtolower(trim((string)$round));
                if (preg_match('/round of (\\d+)/i', $r, $m)) return 10 + (int)$m[1];
                if ($r === 'quarterfinals' || str_contains($r, 'quarter')) return 30;
                if ($r === 'semifinals' || str_contains($r, 'semi')) return 40;
                if ($r === 'finals' || str_contains($r, 'final')) return 50;
                if (preg_match('/round\\s+(\\d+)/i', $r, $m)) return 100 + (int)$m[1];
                if (is_numeric($round)) return (int)$round;
                return 999;
            };

            $roundLabel = function ($roundName, int $slots, int $participants) use ($tournament) {
                if ($tournament->bracket_type === 'round_robin') {
                    $r = strtolower(trim((string) $roundName));
                    if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                        return 'Round ' . (int)$m[1];
                    }
                    return ucfirst($roundName);
                }
                
                $r = strtolower(trim((string) $roundName));
                $basis = max(2, $participants ?: $slots);

                if (preg_match('/round of (\\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
                if (str_contains($r, 'quarter')) return 'Quarterfinals';
                if (str_contains($r, 'semi')) return 'Semifinals';
                if (str_contains($r, 'final')) return 'Finals';

                $idx = null;
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    $idx = (int)$m[1];
                } elseif (preg_match('/^(\d+)$/', $r, $m)) {
                    $idx = (int)$m[1];
                }

                if ($idx !== null) {
                    $progression = \App\Helpers\TournamentRoundHelper::getRoundProgressionForBracketSize($basis);
                    if (!empty($progression) && isset($progression[$idx - 1])) {
                        return $progression[$idx - 1];
                    }
                }

                return ucfirst($roundName);
            };

            $matches = $tournament->matches->unique('id');
            // Backfill winner fields from result if missing (for seeded completed data)
            foreach ($matches as $match) {
                if ($match->status === MatchStatus::COMPLETED->value && (!$match->winner_id || !$match->winner)) {
                    $res = $match->result;
                    if ($res) {
                        $winnerId = $res->winner_id;
                        if (!$winnerId) {
                            $team1Total = ($res->player1_set1_score ?? 0) + ($res->player1_set2_score ?? 0) + ($res->player1_set3_score ?? 0);
                            $team2Total = ($res->player2_set1_score ?? 0) + ($res->player2_set2_score ?? 0) + ($res->player2_set3_score ?? 0);
                            $winnerId = $team1Total >= $team2Total ? $match->player1_id : $match->player2_id;
                        }
                        $winnerPartnerId = null;
                        if ($winnerId === $match->player1_id) {
                            $winnerPartnerId = $match->player1_partner_id;
                        } elseif ($winnerId === $match->player2_id) {
                            $winnerPartnerId = $match->player2_partner_id;
                        }
                        $match->winner_id = $winnerId;
                        $match->winner_partner_id = $winnerPartnerId;
                    }
                }
            }
            // For completed tournaments, ensure matches with results are marked completed
            foreach ($matches as $match) {
                if ($match->result && $match->status !== MatchStatus::COMPLETED->value) {
                    $match->status = MatchStatus::COMPLETED->value;
                }
            }
            if ($tournament->status === TournamentStatus::COMPLETED->value) {
                $matches = $matches->where('status', MatchStatus::COMPLETED->value);
            }
            if ($isSeededTournament) {
                $matches = $matches->filter(function ($match) {
                    $p1Ok = $match->player1 ? str_contains($match->player1->email, 'player.test') : true;
                    $p2Ok = $match->player2 ? str_contains($match->player2->email, 'player.test') : true;
                    $p1pOk = $match->player1Partner ? str_contains($match->player1Partner->email, 'player.test') : true;
                    $p2pOk = $match->player2Partner ? str_contains($match->player2Partner->email, 'player.test') : true;
                    $wOk = $match->winner ? str_contains($match->winner->email, 'player.test') : true;
                    $wpOk = $match->winnerPartner ? str_contains($match->winnerPartner->email, 'player.test') : true;
                    return $p1Ok && $p2Ok && $p1pOk && $p2pOk && $wOk && $wpOk;
                });
            }

            foreach ($matches as $match) {
                $categoryId = $match->tournament_category_id;
                $categorySlots = $tournament->categories->firstWhere('id', $categoryId)?->max_participants ?? ($tournament->categories->firstWhere('id', $categoryId)?->slots ?? 0);
                $participants = $participantCounts[$categoryId] ?? 0;
                $round = $roundLabel($match->round ?? 'Round 1', (int) $categorySlots, (int) $participants);
                
                if (!isset($matchesByCategory[$categoryId])) {
                    $matchesByCategory[$categoryId] = [];
                }
                $matchesByCategory[$categoryId][$round][] = $match;
            }

            // Normalize ordering and deduplicate
            foreach ($matchesByCategory as $catId => $rounds) {
                uksort($rounds, function ($a, $b) use ($roundSorter) {
                    return $roundSorter($a) <=> $roundSorter($b);
                });
                foreach ($rounds as $rName => $roundMatches) {
                    $matchesByCategory[$catId][$rName] = collect($roundMatches)
                        ->unique('id')
                        ->sortBy('match_number')
                        ->values()
                        ->all();
                }
                $matchesByCategory[$catId] = $rounds;
            }
            
            // Re-ensure categories are unique after loading matches (in case matches.category affected it)
            $tournament->setRelation('categories', $tournament->categories->unique('name')->values());
        }

        $standingsByCategory = [];
        if ($tournament->bracket_type === 'round_robin') {
            $standingsService = app(\App\Services\RoundRobinStandingsService::class);
            foreach ($tournament->categories as $category) {
                $standingsByCategory[$category->id] = $standingsService->calculateStandings($category);
            }
        }
        
        $playerRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('player_id', $player->id)
            ->first();
        
        $clubMembership = $player->approvedClubMembership;
        $isEligible = $clubMembership && $clubMembership->status === 'approved';
        
        // Check if player has withdrawn from this tournament (cannot re-register after withdrawal)
        $hasWithdrawn = $playerRegistration && $playerRegistration->status === 'withdrawn';
        
        $canRegister = !$playerRegistration 
            && !$hasWithdrawn
            && $isEligible
            && Carbon::now()->isBefore($tournament->registration_deadline)
            && Carbon::now()->isBefore($tournament->start_date);
        
        // Check if player can withdraw (before withdrawal deadline and tournament hasn't started)
        $canWithdraw = false;
        if ($playerRegistration && in_array($playerRegistration->status, ['approved', 'eligible'])) {
            // Check if withdrawal deadline has passed
            if ($tournament->withdrawal_deadline && Carbon::now()->isBefore($tournament->withdrawal_deadline)) {
                // Check if tournament hasn't started
                if (Carbon::now()->isBefore($tournament->start_date)) {
                    // Check if there's no pending withdrawal request
                    $hasPendingWithdrawal = \App\Models\WithdrawalRequest::where('tournament_registration_id', $playerRegistration->id)
                        ->where('status', 'pending')
                        ->exists();
                    $canWithdraw = !$hasPendingWithdrawal;
                }
            }
        }

        // Partner invitations for doubles/mixed categories (grouped by category for UI)
        $sentInvitations = PartnerInvitation::where('inviter_id', $player->id)
            ->where('tournament_id', $tournament->id)
            ->with(['invitee'])
            ->get()
            ->groupBy('category_id');

        $receivedInvitations = PartnerInvitation::where('invitee_id', $player->id)
            ->where('tournament_id', $tournament->id)
            ->with(['inviter'])
            ->get()
            ->groupBy('category_id');
        
        return view('tournaments.show', compact(
            'tournament',
            'playerRegistration',
            'isEligible',
            'canRegister',
            'canWithdraw',
            'matchesByCategory',
            'registrationsByCategory',
            'standingsByCategory',
            'sentInvitations',
            'receivedInvitations'
        ));
    }
    
    public function showRegistration(Tournament $tournament, TournamentCategory $category): View|RedirectResponse
    {
        $player = auth()->user();
        
        if ($tournament->status === TournamentStatus::CANCELLED->value) {
            abort(404, 'Tournament not found.');
        }
        
        if ($category->tournament_id !== $tournament->id) {
            abort(404, 'Category not found for this tournament.');
        }
        
        $playerGender = strtolower($player->gender ?? '');
        $categoryType = strtoupper($category->type ?? '');
        
        $isMenCategory = ($categoryType === 'MS' || $categoryType === 'MD');
        $isWomenCategory = ($categoryType === 'WS' || $categoryType === 'WD');
        $isMixedCategory = ($categoryType === 'XD');
        
        if ($isMenCategory && !$isMixedCategory && $playerGender !== 'male') {
            return redirect()->route('player.tournaments.show', $tournament->id)
                ->with('error', 'You are not eligible for this category. This category is for men only.');
        }
        
        if ($isWomenCategory && !$isMixedCategory && $playerGender !== 'female') {
            return redirect()->route('player.tournaments.show', $tournament->id)
                ->with('error', 'You are not eligible for this category. This category is for women only.');
        }
        
        $clubMembership = $player->approvedClubMembership;
        $isEligible = $clubMembership && $clubMembership->status === 'approved';
        
        $playerRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('player_id', $player->id)
            ->where('category_id', $category->id)
            ->first();
        
        // Check if player has withdrawn from this category (cannot re-register after withdrawal)
        $hasWithdrawn = $playerRegistration && $playerRegistration->status === 'withdrawn';
        
        // Check for same-date tournament conflict (only approved registrations, exclude withdrawn)
        $hasSameDateRegistration = TournamentRegistration::where('player_id', $player->id)
            ->where('status', 'approved')
            ->whereHas('tournament', function($query) use ($tournament) {
                $query->where('start_date', $tournament->start_date)
                      ->where('id', '!=', $tournament->id)
                      ->whereIn('status', ['published', 'upcoming', 'ongoing']);
            })
            ->exists();
        
        $canRegister = !$playerRegistration 
            && !$hasWithdrawn
            && !$hasSameDateRegistration
            && $isEligible
            && Carbon::now()->isBefore($tournament->registration_deadline)
            && Carbon::now()->isBefore($tournament->start_date);
        
        if (!$canRegister) {
            $errorMessage = 'You cannot register for this category at this time.';
            if ($hasSameDateRegistration) {
                $errorMessage = 'You are already registered for an upcoming tournament on this date.';
            }
            return redirect()->route('player.tournaments.show', $tournament->id)
                ->with('error', $errorMessage);
        }
        
        $eligibilityService = app(EligibilityService::class);
        $eligibility = $eligibilityService->checkEligibility($player, $category);
        
        $eloService = app(\App\Services\EloRatingService::class);
        $categoryType = $category->type ?? 'MS';
        $playerElo = $eloService->getCurrentRating($player, $categoryType);
        
        $eloRequirement = null;
        if ($category->skill_level && $category->skill_level !== 'Open') {
            $eloRanges = [
                'A' => ['min' => 1800, 'max' => null],
                'B' => ['min' => 1500, 'max' => 1799],
                'C' => ['min' => 1200, 'max' => 1499],
                'D' => ['min' => 0, 'max' => 1199],
            ];
            $eloRequirement = $eloRanges[$category->skill_level] ?? null;
        }

        // Partner invitations for this category (doubles/mixed support)
        $sentInvitations = PartnerInvitation::where('inviter_id', $player->id)
            ->where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->with(['invitee'])
            ->get();

        $receivedInvitations = PartnerInvitation::where('invitee_id', $player->id)
            ->where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->with(['inviter'])
            ->get();

        $acceptedInvitation = PartnerInvitation::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where('status', 'accepted')
            ->where(function($q) use ($player) {
                $q->where('inviter_id', $player->id)->orWhere('invitee_id', $player->id);
            })
            ->with(['inviter', 'invitee'])
            ->first();
        
        $partnerCandidate = null;
        if ($playerRegistration && $playerRegistration->partner) {
            // Only show partner if registration is not withdrawn
            if ($playerRegistration->status !== 'withdrawn') {
                // Check if partner's registration is also not withdrawn
                $partnerRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
                    ->where('category_id', $category->id)
                    ->where(function($q) use ($playerRegistration) {
                        $q->where('player_id', $playerRegistration->partner_id)
                          ->orWhere('partner_id', $playerRegistration->partner_id);
                    })
                    ->where('status', '!=', 'withdrawn')
                    ->first();
                
                if ($partnerRegistration) {
                    $partnerCandidate = $playerRegistration->partner;
                }
            }
        } elseif ($acceptedInvitation) {
            $partnerCandidate = $acceptedInvitation->inviter_id === $player->id
                ? $acceptedInvitation->invitee
                : $acceptedInvitation->inviter;
        }
        
        // Get payment contact information
        $paymentContactEmail = $tournament->contact_email ?? ($tournament->club?->contact_email ?? ($tournament->club?->manager?->email ?? null));
        $paymentContactPhone = $tournament->contact_phone ?? ($tournament->club?->contact_phone ?? ($tournament->club?->manager?->contact_number ?? null));
        
        return view('tournaments.register', compact(
            'tournament',
            'playerElo',
            'eloRequirement',
            'category',
            'eligibility',
            'paymentContactEmail',
            'paymentContactPhone',
            'sentInvitations',
            'receivedInvitations',
            'partnerCandidate'
        ));
    }
}
