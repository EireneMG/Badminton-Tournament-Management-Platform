<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubPlayer extends Model
{
    protected $fillable = [
        'club_id',
        'player_id',
        'status',
        'request_type',
        'skill_level',
        'provisional_elo',
        'is_provisional',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }
}
