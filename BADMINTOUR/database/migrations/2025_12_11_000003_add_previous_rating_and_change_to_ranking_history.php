<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds previous_rating and change columns to ranking_history table.
     * These columns are essential for:
     * 1. Tracking ELO rating changes (previous_rating shows what it was before, change shows the delta)
     * 2. Walkover penalties (change = -25 to show penalty amount)
     * 3. Historical analytics and audit trails
     * 4. Displaying rating changes in player profiles
     * 
     * The migration is idempotent - it checks if columns exist before adding them.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('ranking_history', 'previous_rating')) {
            Schema::table('ranking_history', function (Blueprint $table) {
                $table->integer('previous_rating')->nullable()->after('rating');
            });
        }
        
        if (!Schema::hasColumn('ranking_history', 'change')) {
            Schema::table('ranking_history', function (Blueprint $table) {
                $table->integer('change')->nullable()->after('previous_rating');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranking_history', function (Blueprint $table) {
            if (Schema::hasColumn('ranking_history', 'change')) {
                $table->dropColumn('change');
            }
            if (Schema::hasColumn('ranking_history', 'previous_rating')) {
                $table->dropColumn('previous_rating');
            }
        });
    }
};

