<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('matches', function (Blueprint $table) {
                $table->index('player1_id');
                $table->index('player2_id');
                $table->index('winner_id');
            });
        } catch (\Exception $e) {
        }

        try {
            Schema::table('tournament_registrations', function (Blueprint $table) {
                $table->index('player_id');
                $table->index('tournament_id');
            });
        } catch (\Exception $e) {
        }

        try {
            Schema::table('elo_ratings', function (Blueprint $table) {
                $table->index('player_id');
                $table->index('category');
            });
        } catch (\Exception $e) {
        }
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['player1_id']);
            $table->dropIndex(['player2_id']);
            $table->dropIndex(['winner_id']);
        });

        Schema::table('tournament_registrations', function (Blueprint $table) {
            $table->dropIndex(['player_id']);
            $table->dropIndex(['tournament_id']);
        });

        Schema::table('elo_ratings', function (Blueprint $table) {
            $table->dropIndex(['player_id']);
            $table->dropIndex(['category']);
        });
    }

};
