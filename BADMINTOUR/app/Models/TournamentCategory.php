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
        'schedule_start_date',
        'schedule_start_time',
        'match_duration_minutes',
        'break_between_matches_minutes',
    ];

    protected $appends = ['slots', 'type', 'match_type', 'skill_level_requirements', 'age_requirement'];
    
    protected $casts = [
        'schedule_start_date' => 'date',
        'schedule_start_time' => 'datetime',
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

    public function getSlotsAttribute(): int
    {
        return $this->max_participants ?? 32;
    }

    public function getTypeAttribute(): string
    {
        $name = strtolower($this->name ?? '');
        if (str_contains($name, 'mixed')) {
            return 'XD';
        } elseif (str_contains($name, "men's doubles") || str_contains($name, 'mens doubles')) {
            return 'MD';
        } elseif (str_contains($name, "women's doubles") || str_contains($name, 'womens doubles')) {
            return 'WD';
        } elseif (str_contains($name, "men's singles") || str_contains($name, 'mens singles')) {
            return 'MS';
        } elseif (str_contains($name, "women's singles") || str_contains($name, 'womens singles')) {
            return 'WS';
        } elseif (str_contains($name, 'singles')) {
            return 'MS';
        } elseif (str_contains($name, 'doubles')) {
            return 'MD';
        }
        return $this->name ?? 'MS';
    }
    
    /**
     * Get full category name (always returns full name like "Men's Singles")
     */
    public function getFullNameAttribute(): string
    {
        // If name already contains full format, return it
        $name = $this->name ?? '';
        if (stripos($name, "Men's Singles") !== false || stripos($name, "Women's Singles") !== false || 
            stripos($name, "Men's Doubles") !== false || stripos($name, "Women's Doubles") !== false ||
            stripos($name, "Mixed Doubles") !== false) {
            return $name;
        }
        
        // Otherwise, convert type code to full name
        $type = $this->type;
        $map = [
            'MS' => "Men's Singles",
            'WS' => "Women's Singles",
            'MD' => "Men's Doubles",
            'WD' => "Women's Doubles",
            'XD' => "Mixed Doubles",
        ];
        
        return $map[$type] ?? $name;
    }

    public function getMatchTypeAttribute(): string
    {
        $name = strtolower($this->name ?? '');
        if (str_contains($name, 'singles')) {
            return 'Singles';
        } elseif (str_contains($name, 'doubles')) {
            return 'Doubles';
        }
        return $this->name ?? 'Singles';
    }

    public function getSkillLevelRequirementsAttribute(): ?string
    {
        if (!$this->skill_level) {
            return null;
        }
        $levels = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate and above',
            'advanced' => 'Advanced',
            'all' => 'All levels welcome',
        ];
        return $levels[$this->skill_level] ?? ucfirst($this->skill_level);
    }
    
    /**
     * Get age requirement as a formatted string from min_age and max_age
     * Converts min_age/max_age back to age bracket format for display
     */
    public function getAgeRequirementAttribute(): string
    {
        // Junior: max_age = 17 (Under 18)
        if ($this->max_age === 17 && $this->min_age === null) {
            return 'Junior (Under 18)';
        }
        
        // Senior: min_age = 18 (18+)
        if ($this->min_age === 18 && $this->max_age === null) {
            return 'Senior (18+)';
        }
        
        // Open: both null (All Ages)
        if ($this->min_age === null && $this->max_age === null) {
            return 'Open (All Ages)';
        }
        
        // Legacy format: if min_age and max_age are set, format as range
        if ($this->min_age !== null && $this->max_age !== null) {
            return "Ages {$this->min_age}-{$this->max_age}";
        }
        
        // If only min_age is set
        if ($this->min_age !== null) {
            return "Ages {$this->min_age}+";
        }
        
        // If only max_age is set
        if ($this->max_age !== null) {
            return "Ages up to {$this->max_age}";
        }
        
        return 'Open (All Ages)';
    }
}
