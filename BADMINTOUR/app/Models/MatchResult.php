<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResult extends Model
{
    protected $fillable = [
        'match_id',
        'winner_id',
        'winner_partner_id',
        'player1_set1_score',
        'player2_set1_score',
        'player1_set2_score',
        'player2_set2_score',
        'player1_set3_score',
        'player2_set3_score',
        'score_inputted_by',
        'inputted_by_user_id',
        'is_walkover',
        'elo_updated',
    ];
    
    protected $casts = [
        'is_walkover' => 'boolean',
        'elo_updated' => 'boolean',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function winnerPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_partner_id');
    }

    public function inputtedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inputted_by_user_id');
    }
}
