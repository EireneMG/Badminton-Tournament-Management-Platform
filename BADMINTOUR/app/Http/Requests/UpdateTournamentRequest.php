<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tournament;

class UpdateTournamentRequest extends FormRequest
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
        
        $tournament = $this->route('tournament');
        
        if (!$tournament || $tournament->club->manager_id !== $user->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'venue_name' => ['sometimes', 'string', 'max:255'],
            'number_of_courts' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'contact_email' => ['sometimes', 'email', 'max:255'],
            'contact_phone' => ['sometimes', 'string', 'max:20'],
            'tournament_fee' => ['sometimes', 'numeric', 'min:0'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
