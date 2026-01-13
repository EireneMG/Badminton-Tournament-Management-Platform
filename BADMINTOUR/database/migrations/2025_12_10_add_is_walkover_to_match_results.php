<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('match_results', function (Blueprint $table) {
            // Add is_walkover column if it doesn't already exist
            if (!Schema::hasColumn('match_results', 'is_walkover')) {
                $table->boolean('is_walkover')->default(false)->after('inputted_by_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_results', function (Blueprint $table) {
            if (Schema::hasColumn('match_results', 'is_walkover')) {
                $table->dropColumn('is_walkover');
            }
        });
    }
};
