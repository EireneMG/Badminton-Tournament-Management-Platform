<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Club;
use App\Models\ClubPlayer;
use Carbon\Carbon;

class StoreTournamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if (!$user || $user->role !== 'manager') {
            return false;
        }
        
        $club = Club::where('manager_id', $user->id)->first();
        
        if (!$club) {
            return false;
        }
        
        $activePlayersCount = ClubPlayer::where('club_id', $club->id)
            ->where('status', 'approved')
            ->count();
        
        if ($activePlayersCount < 5) {
            session()->flash('error', 'Your club must have at least 5 active players to create a tournament.');
            return false;
        }
        
        return true;
    }

}