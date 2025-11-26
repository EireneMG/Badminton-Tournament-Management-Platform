<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\ClubPlayer;
use Carbon\Carbon;

class RegisterTournamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if (!$user || $user->role !== 'player') {
            return false;
        }
        
        $clubMembership = ClubPlayer::where('player_id', $user->id)
            ->where('status', 'approved')
            ->first();
        
        if (!$clubMembership) {
            session()->flash('error', 'You must be a member of a club to register for tournaments.');
            return false;
        }
        
        $category = TournamentCategory::find($this->category_id);
        
        if (!$category) {
            return false;
        }
        
        $alreadyRegistered = TournamentRegistration::where('player_id', $user->id)
            ->where('category_id', $category->id)
            ->whereIn('status', ['pending', 'paid', 'approved'])
            ->exists();
        
        if ($alreadyRegistered) {
            session()->flash('error', 'You are already registered for this category.');
            return false;
        }
        
        if ($this->checkAgeRequirement($user, $category) === false) {
            session()->flash('error', 'You do not meet the age requirement for this category.');
            return false;
        }
        
        if ($this->checkSkillRequirement($user, $category) === false) {
            session()->flash('error', 'You do not meet the skill level requirement for this category.');
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
