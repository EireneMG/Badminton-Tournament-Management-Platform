<?php

namespace App\Services;

use App\Models\EloRating;
use App\Models\RankingHistory;
use App\Models\User;
use Carbon\Carbon;

class EloRatingService
{
    const K_FACTOR = 32;
}