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

    
}
