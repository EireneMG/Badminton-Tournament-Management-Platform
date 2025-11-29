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
        
        // If manager doesn't have a club yet, redirect to create club page
        if (!$club) {
            return view('manager.tournaments', ['tournaments' => collect([]), 'club' => null]);
        }
        
        $tournaments = Tournament::where('club_id', $club->id)
            ->with('categories')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manager.tournaments', compact('tournaments', 'club'));
    }

    public function create(): View
    {
        // Check if manager is verified
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can create tournaments. Please wait for verification or contact support if you have questions.');
        }

        return view('manager.tournaments.create');
    }

    public function store(StoreTournamentRequest $request): RedirectResponse
    {
        // Check if manager is verified
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can create tournaments. Please wait for verification or contact support if you have questions.');
        }

        DB::beginTransaction();
        
        try {
            $club = Club::where('manager_id', auth()->id())->first();
            
            $tournamentData = $request->except(['banner', 'categories']);
            $tournamentData['club_id'] = $club->id;
            
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('tournament-banners', 'public');
                $tournamentData['banner_path'] = $bannerPath;
            }
            
            $tournament = Tournament::create($tournamentData);
            
            foreach ($request->categories as $categoryData) {
                TournamentCategory::create([
                    'tournament_id' => $tournament->id,
                    'type' => $categoryData['type'],
                    'slots' => $categoryData['slots'],
                    'skill_level_requirements' => $categoryData['skill_level_requirements'] ?? null,
                    'age_requirement' => $categoryData['age_requirement'] ?? null,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('manager.tournaments.show', $tournament->id)
                ->with('success', 'Tournament created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Failed to create tournament: ' . $e->getMessage());
        }
    }

    public function show(Tournament $tournament): View
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        $tournament->load(['categories', 'registrations.player', 'matches']);
        
        return view('manager.tournaments.show', compact('tournament'));
    }

    public function edit(Tournament $tournament): View
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        $tournament->load('categories');
        
        return view('manager.tournaments.edit', compact('tournament'));
    }

    public function update(UpdateTournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        try {
            $updateData = $request->except('banner');
            
            if ($request->hasFile('banner')) {
                if ($tournament->banner_path) {
                    Storage::disk('public')->delete($tournament->banner_path);
                }
                
                $bannerPath = $request->file('banner')->store('tournament-banners', 'public');
                $updateData['banner_path'] = $bannerPath;
            }
            
            $tournament->update($updateData);
            
            return redirect()->route('manager.tournaments.show', $tournament->id)
                ->with('success', 'Tournament updated successfully!');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update tournament: ' . $e->getMessage());
        }
    }

    public function destroy(Tournament $tournament): RedirectResponse
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        if ($tournament->registrations()->count() > 0) {
            return back()->with('error', 'Cannot delete tournament with existing registrations.');
        }
        
        if ($tournament->banner_path) {
            Storage::disk('public')->delete($tournament->banner_path);
        }
        
        $tournament->delete();
        
        return redirect()->route('manager.tournaments.index')
            ->with('success', 'Tournament deleted successfully!');
    }

    public function publish(Tournament $tournament): RedirectResponse
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        if ($tournament->categories()->count() === 0) {
            return back()->with('error', 'Cannot publish tournament without categories.');
        }
        
        $tournament->update(['status' => 'published']);
        
        return back()->with('success', 'Tournament published successfully!');
    }

    public function generateMatches(Tournament $tournament): RedirectResponse
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        if ($tournament->status !== 'published') {
            return back()->with('error', 'Tournament must be published before generating matches.');
        }
        
        try {
            $this->matchGenerationService->generateMatches($tournament);
            
            $tournament->update(['status' => 'ongoing']);
            
            $registrations = $tournament->registrations()->where('status', 'approved')->get();
            foreach ($registrations as $registration) {
                Notification::create([
                    'user_id' => $registration->player_id,
                    'type' => 'match_scheduled',
                    'title' => 'Tournament Matches Generated',
                    'message' => "Matches have been generated for {$tournament->name}. Check your schedule!",
                    'data' => ['tournament_id' => $tournament->id],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
            }
            
            return back()->with('success', 'Matches generated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate matches: ' . $e->getMessage());
        }
    }
}
