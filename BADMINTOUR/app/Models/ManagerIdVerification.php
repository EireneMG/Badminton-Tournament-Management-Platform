<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerIdVerification extends Model
{
    protected $fillable = [
        'manager_id',
        'id_type',
        'id_file_path',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
