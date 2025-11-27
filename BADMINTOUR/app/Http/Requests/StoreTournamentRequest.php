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

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $minStartDate = Carbon::now()->addDays(7)->format('Y-m-d');
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'venue_name' => ['required', 'string', 'max:255'],
            'number_of_courts' => ['required', 'integer', 'min:1', 'max:50'],
            'start_date' => ['required', 'date', 'after_or_equal:' . $minStartDate],
            'end_date' => ['required', 'date', 'after:start_date'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'facebook_link' => ['nullable', 'url', 'max:255'],
            'tournament_fee' => ['required', 'numeric', 'min:0'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.type' => ['required', 'string', 'in:MS,WS,MD,WD,XD'],
            'categories.*.slots' => ['required', 'integer', 'min:2', 'max:128'],
            'categories.*.skill_level_requirements' => ['nullable', 'string', 'max:255'],
            'categories.*.age_requirement' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'The tournament must start at least 7 days from today.',
            'categories.required' => 'At least one tournament category is required.',
            'categories.*.type.in' => 'Category type must be one of: MS (Men Singles), WS (Women Singles), MD (Men Doubles), WD (Women Doubles), XD (Mixed Doubles).',
        ];
    }
}

