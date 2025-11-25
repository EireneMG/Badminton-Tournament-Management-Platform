<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\Club;
use App\Models\Notification;
use App\Services\MatchGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    protected $matchGenerationService;

    public function __construct(MatchGenerationService $matchGenerationService)
    {
        $this->matchGenerationService = $matchGenerationService;
    }

    public function index(): View
    {
        $club = Club::where('manager_id', auth()->id())->first();
        
        $tournaments = Tournament::where('club_id', $club->id)
            ->with('categories')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manager.tournaments', compact('tournaments', 'club'));
    }

}