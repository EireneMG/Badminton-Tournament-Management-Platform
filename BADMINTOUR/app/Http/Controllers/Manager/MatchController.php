<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use App\Models\MatchResult;
use App\Models\Notification;
use App\Services\EloRatingService;
use App\Services\MatchGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MatchController extends Controller
{
    protected $eloRatingService;
    protected $matchGenerationService;

    public function __construct(EloRatingService $eloRatingService, MatchGenerationService $matchGenerationService)
    {
        $this->eloRatingService = $eloRatingService;
        $this->matchGenerationService = $matchGenerationService;
    }

    public function index()
    {
        $club = \App\Models\Club::where('manager_id', auth()->id())->first();
        
        // If manager doesn't have a club yet, show empty state
        if (!$club) {
            return view('manager.matches', [
                'tournaments' => collect([]), 
                'categories' => collect([]), 
                'club' => null
            ]);
        }
        
        $tournaments = \App\Models\Tournament::where('club_id', $club->id)
            ->with(['categories', 'matches.player1', 'matches.player2', 'matches.category'])
            ->get();
        
        $categories = \App\Models\TournamentCategory::whereIn(
            'tournament_id', 
            $tournaments->pluck('id')
        )->get();
        
        return view('manager.matches', compact('tournaments', 'categories', 'club'));
    }

    public function updateScore(TournamentMatch $match, Request $request): RedirectResponse
    {
        if ($match->tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $request->validate([
            'player1_score' => ['required', 'integer', 'min:0'],
            'player2_score' => ['required', 'integer', 'min:0'],
            'set1_player1' => ['nullable', 'integer', 'min:0'],
            'set1_player2' => ['nullable', 'integer', 'min:0'],
            'set2_player1' => ['nullable', 'integer', 'min:0'],
            'set2_player2' => ['nullable', 'integer', 'min:0'],
            'set3_player1' => ['nullable', 'integer', 'min:0'],
            'set3_player2' => ['nullable', 'integer', 'min:0'],
        ]);
        
        $winnerId = $request->player1_score > $request->player2_score 
            ? $match->player1_id 
            : $match->player2_id;
        
        $winnerPartnerId = $winnerId === $match->player1_id 
            ? $match->player1_partner_id 
            : $match->player2_partner_id;
        
        MatchResult::updateOrCreate(
            ['match_id' => $match->id],
            [
                'player1_score' => $request->player1_score,
                'player2_score' => $request->player2_score,
                'set1_player1' => $request->set1_player1,
                'set1_player2' => $request->set1_player2,
                'set2_player1' => $request->set2_player1,
                'set2_player2' => $request->set2_player2,
                'set3_player1' => $request->set3_player1,
                'set3_player2' => $request->set3_player2,
                'winner_id' => $winnerId,
            ]
        );
        
        $match->update([
            'status' => 'completed',
            'winner_id' => $winnerId,
            'winner_partner_id' => $winnerPartnerId,
        ]);
        
        $category = $match->category;
        $player1 = $match->player1;
        $player2 = $match->player2;
        
        if (in_array($category->type, ['MD', 'WD', 'XD']) && $match->player1_partner_id && $match->player2_partner_id) {
            $player1Partner = $match->player1Partner;
            $player2Partner = $match->player2Partner;
            
            $this->eloRatingService->calculateDoublesMatchRatings(
                $player1, 
                $player1Partner, 
                $player2, 
                $player2Partner,
                $winnerId === $player1->id,
                $category->type
            );
        } else {
            $this->eloRatingService->calculateMatchRatings(
                $player1,
                $player2,
                $winnerId === $player1->id,
                $category->type
            );
        }
        
        $this->matchGenerationService->advanceWinner($match);
        
        Notification::create([
            'user_id' => $player1->id,
            'type' => 'match_result_posted',
            'title' => 'Match Result Posted',
            'message' => "The result for your match in {$match->tournament->name} has been posted.",
            'data' => ['match_id' => $match->id],
            'action_url' => route('player.matches.show', $match->id),
        ]);
        
        Notification::create([
            'user_id' => $player2->id,
            'type' => 'match_result_posted',
            'title' => 'Match Result Posted',
            'message' => "The result for your match in {$match->tournament->name} has been posted.",
            'data' => ['match_id' => $match->id],
            'action_url' => route('player.matches.show', $match->id),
        ]);
        
        return back()->with('success', 'Match score updated and winner advanced successfully!');
    }

    public function reschedule(TournamentMatch $match, Request $request): RedirectResponse
    {
        if ($match->tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        if ($match->reschedule_count >= 1) {
            return back()->with('error', 'This match has already been rescheduled once. No further rescheduling allowed.');
        }
        
        $request->validate([
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required'],
            'court_number' => ['required', 'integer', 'min:1'],
        ]);
        
        $match->update([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'court_number' => $request->court_number,
            'reschedule_count' => $match->reschedule_count + 1,
        ]);
        
        if ($match->player1_id) {
            Notification::create([
                'user_id' => $match->player1_id,
                'type' => 'match_rescheduled',
                'title' => 'Match Rescheduled',
                'message' => "Your match in {$match->tournament->name} has been rescheduled.",
                'data' => ['match_id' => $match->id],
                'action_url' => route('player.matches.show', $match->id),
            ]);
        }
        
        if ($match->player2_id) {
            Notification::create([
                'user_id' => $match->player2_id,
                'type' => 'match_rescheduled',
                'title' => 'Match Rescheduled',
                'message' => "Your match in {$match->tournament->name} has been rescheduled.",
                'data' => ['match_id' => $match->id],
                'action_url' => route('player.matches.show', $match->id),
            ]);
        }
        
        return back()->with('success', 'Match rescheduled successfully!');
    }
}
