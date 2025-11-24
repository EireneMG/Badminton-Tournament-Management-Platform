<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentCategory extends Model
{
    protected $fillable = [
        'tournament_id',
        'name',
        'min_age',
        'max_age',
        'gender',
        'skill_level',
        'max_participants',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class, 'category_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class, 'category_id');
    }
}
