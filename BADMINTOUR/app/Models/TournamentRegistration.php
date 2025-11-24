<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TournamentRegistration extends Model
{
    protected $fillable = [
        'tournament_id',
        'player_id',
        'category_id',
        'partner_id',
        'status',
        'payment_verified_at',
    ];

    protected $casts = [
        'payment_verified_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'category_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function withdrawalRequest(): HasOne
    {
        return $this->hasOne(WithdrawalRequest::class);
    }
}
