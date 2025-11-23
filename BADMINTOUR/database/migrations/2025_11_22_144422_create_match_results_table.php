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
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('player1_set1_score')->nullable();
            $table->integer('player2_set1_score')->nullable();
            $table->integer('player1_set2_score')->nullable();
            $table->integer('player2_set2_score')->nullable();
            $table->integer('player1_set3_score')->nullable();
            $table->integer('player2_set3_score')->nullable();
            $table->enum('score_inputted_by', ['manager', 'assistant', 'referee'])->default('manager');
            $table->foreignId('inputted_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
