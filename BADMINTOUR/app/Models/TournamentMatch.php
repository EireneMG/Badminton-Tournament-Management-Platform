<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TournamentMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'tournament_category_id',
        'player1_id',
        'player2_id',
        'player1_partner_id',
        'player2_partner_id',
        'winner_id',
        'winner_partner_id',
        'round',
        'bracket_position',
        'match_number',
        'scheduled_date',
        'scheduled_time',
        'court_number',
        'status',
        'reschedule_count',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'tournament_category_id');
    }
    
    public function player1Partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_partner_id');
    }
    
    public function player2Partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_partner_id');
    }
    
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
    
    public function winnerPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_partner_id');
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(MatchResult::class, 'match_id');
    }
}
