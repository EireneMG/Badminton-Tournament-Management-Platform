<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'organizer_id',
        'club_id',
        'type',
        'start_date',
        'end_date',
        'registration_deadline',
        'withdrawal_deadline',
        'location',
        'registration_fee',
        'status',
        'is_dual_meet',
        'archived',
        'facebook_link',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'withdrawal_deadline' => 'date',
        'registration_fee' => 'decimal:2',
        'is_dual_meet' => 'boolean',
        'archived' => 'boolean',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TournamentCategory::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }
}
