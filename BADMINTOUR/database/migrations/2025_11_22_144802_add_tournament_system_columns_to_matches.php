<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->renameColumn('category_id', 'tournament_category_id');
        });
        
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('player1_partner_id')->nullable()->after('player1_id')->constrained('users')->onDelete('set null');
            $table->foreignId('player2_partner_id')->nullable()->after('player2_id')->constrained('users')->onDelete('set null');
            $table->foreignId('winner_id')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->foreignId('winner_partner_id')->nullable()->after('winner_id')->constrained('users')->onDelete('set null');
            $table->integer('match_number')->default(1)->after('round');
            $table->date('scheduled_date')->nullable()->after('bracket_position');
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
            $table->integer('court_number')->nullable()->after('scheduled_time');
            $table->integer('reschedule_count')->default(0)->after('status');
            $table->dropColumn('scheduled_at');
            $table->dropColumn('rescheduled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['player1_partner_id']);
            $table->dropForeign(['player2_partner_id']);
            $table->dropForeign(['winner_id']);
            $table->dropForeign(['winner_partner_id']);
            $table->dropColumn(['player1_partner_id', 'player2_partner_id', 'winner_id', 'winner_partner_id', 'match_number', 'scheduled_date', 'scheduled_time', 'court_number', 'reschedule_count']);
            $table->dateTime('scheduled_at')->nullable();
            $table->boolean('rescheduled')->default(false);
            $table->renameColumn('tournament_category_id', 'category_id');
        });
    }
};
