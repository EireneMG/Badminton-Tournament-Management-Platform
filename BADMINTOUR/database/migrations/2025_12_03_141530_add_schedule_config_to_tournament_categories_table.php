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
        Schema::table('tournament_categories', function (Blueprint $table) {
            $table->date('schedule_start_date')->nullable()->after('max_participants');
            $table->time('schedule_start_time')->nullable()->after('schedule_start_date');
            $table->integer('match_duration_minutes')->default(45)->after('schedule_start_time');
            $table->integer('break_between_matches_minutes')->default(5)->after('match_duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_categories', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_start_date',
                'schedule_start_time',
                'match_duration_minutes',
                'break_between_matches_minutes',
            ]);
        });
    }
};
