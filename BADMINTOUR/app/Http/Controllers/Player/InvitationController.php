<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\PartnerInvitation;
use App\Models\Tournament;
use App\Models\User;
use App\Models\ClubPlayer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\EloRatingService;

class InvitationController extends Controller
{
    /**
     * Show invitation management page
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Get invitations sent by user
        $sentInvitations = PartnerInvitation::where('inviter_id', $user->id)
            ->with(['tournament', 'category', 'invitee'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get invitations received by user
        $receivedInvitations = PartnerInvitation::where('invitee_id', $user->id)
            ->with(['tournament', 'category', 'inviter'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filter options
        $statusFilter = $request->get('status', 'all');
        $tournamentFilter = $request->get('tournament_id');
        
        if ($statusFilter !== 'all') {
            $sentInvitations = $sentInvitations->where('status', $statusFilter);
            $receivedInvitations = $receivedInvitations->where('status', $statusFilter);
        }
        
        if ($tournamentFilter) {
            $sentInvitations = $sentInvitations->where('tournament_id', $tournamentFilter);
            $receivedInvitations = $receivedInvitations->where('tournament_id', $tournamentFilter);
        }
        
        $tournaments = Tournament::whereHas('invitations', function($query) use ($user) {
            $query->where('inviter_id', $user->id)
                  ->orWhere('invitee_id', $user->id);
        })->get();
        
        return view('player.invitations.index', compact('sentInvitations', 'receivedInvitations', 'tournaments', 'statusFilter', 'tournamentFilter'));
    }
    
    /**
     * Show partner search page
     */
    public function search(Request $request, Tournament $tournament, $categoryId): View
    {
        $user = auth()->user();
        $category = $tournament->categories()->findOrFail($categoryId);
        $eloService = app(EloRatingService::class);
        $eloGap = config('elo.gap', 350);
        $userElo = $eloService->getCurrentRating($user, $category->type ?? 'MD');

        // If user already has a partner for this category, block search
        $hasPartner = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->where('player_id', $user->id)
            ->whereNotNull('partner_id')
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->exists();
        if ($hasPartner) {
            return redirect()->route('player.tournaments.register.show', [$tournament->id, $categoryId])
                ->with('error', 'You already have a partner for this category.');
        }
        
        $search = $request->get('search');
        
        // Build query for eligible partners
        $query = User::where('role', 'player')
            ->where('id', '!=', $user->id)
            ->whereHas('clubMemberships', function($q) {
                $q->where('status', 'approved');
            });
        
        // Exclude already registered players (or partnered) and those already accepted elsewhere in this category
        $registeredPlayerIds = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->pluck('player_id')
            ->merge(
                \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
                    ->whereIn('status', ['pending', 'eligible', 'approved'])
                    ->whereNotNull('partner_id')
                    ->pluck('partner_id')
            )
            ->unique();

        $acceptedInvitationIds = PartnerInvitation::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->where('status', 'accepted')
            ->get()
            ->flatMap(function($inv) {
                return [$inv->inviter_id, $inv->invitee_id];
            })
            ->unique();
        
        $query->whereNotIn('id', $registeredPlayerIds)
              ->whereNotIn('id', $acceptedInvitationIds);
        
        // Interclub: exclude same club
        if ($tournament->is_dual_meet) {
            // For interclub: exclude players from same club
            $userClub = ClubPlayer::where('player_id', $user->id)
                ->where('status', 'approved')
                ->first();
            
            if ($userClub) {
                $query->whereHas('clubMemberships', function($q) use ($userClub) {
                    $q->where('club_id', '!=', $userClub->club_id)
                      ->where('status', 'approved');
                });
            }
        }
        
        // Gender rule by category type (strict, including mixed)
        // Use category type attribute which correctly maps MS/WS/MD/WD/XD
        $catType = strtoupper($category->type ?? '');
        $catName = strtolower($category->name ?? '');
        $catMatch = strtolower($category->match_type ?? '');
        
        // Determine category type - prioritize type attribute, fallback to name
        $isMixed = $catType === 'XD' || str_contains($catName, 'mixed') || str_contains($catMatch, 'mixed');
        $forceMale = $catType === 'MD' || str_contains($catName, "men's") || str_contains($catName, 'mens') || str_contains($catMatch, 'men');
        $forceFemale = $catType === 'WD' || str_contains($catName, "women's") || str_contains($catName, 'womens') || str_contains($catMatch, 'women');
        $userGender = $user->gender ?? ''; // Keep original capitalization (Male/Female)

        if ($isMixed) {
            // For mixed doubles: show opposite gender only
            if ($userGender === 'Male') {
                $query->where('gender', 'Female');
            } elseif ($userGender === 'Female') {
                $query->where('gender', 'Male');
            }
        } elseif ($forceMale) {
            // For men's doubles: show males only
            $query->where('gender', 'Male');
        } elseif ($forceFemale) {
            // For women's doubles: show females only
            $query->where('gender', 'Female');
        }

        // Skill-level restriction: exact match unless open/all
        $skillReq = strtolower($category->skill_level ?? '');
        if ($skillReq && !in_array($skillReq, ['open','all','any'])) {
            $query->whereRaw('LOWER(COALESCE(skill_level, "")) = ?', [$skillReq]);
        }
        
        // Search by name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        $players = $query->with(['clubMemberships.club'])->get();

        // ELO window filter (automatic) and sort by closest ELO
        $categoryType = $category->type ?? 'MD';
        $userElo = $eloService->getCurrentRating($user, $categoryType);
        $players = $players->map(function($p) use ($eloService, $categoryType, $userElo) {
            $elo = $eloService->getCurrentRating($p, $categoryType);
            $p->elo_value = $elo;
            $p->elo_diff = abs($elo - $userElo);
            return $p;
        })->filter(function($p) use ($eloGap) {
            return $p->elo_diff <= $eloGap;
        })->sortBy('elo_diff'); // Sort by closest ELO first

        // Paginate manually (simple)
        $players = $players->values();
        $perPage = 20;
        $page = max((int)request('page', 1), 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $players->forPage($page, $perPage),
            $players->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        $clubs = \App\Models\Club::whereHas('clubPlayers', function($q) {
            $q->where('status', 'approved');
        })->get();
        
        return view('player.invitations.search', [
            'tournament' => $tournament,
            'category' => $category,
            'players' => $paginated,
            'clubs' => $clubs,
            'search' => $search,
            'eloGap' => $eloGap,
            'userElo' => $userElo,
        ]);
    }
}

