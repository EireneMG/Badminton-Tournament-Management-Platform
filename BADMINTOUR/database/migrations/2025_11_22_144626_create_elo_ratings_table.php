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
        Schema::create('elo_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('users')->onDelete('cascade');
            $table->string('category');
            $table->integer('current_rating')->default(1200);
            $table->integer('peak_rating')->default(1200);
            $table->integer('matches_played')->default(0);
            $table->timestamps();
            
            $table->unique(['player_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elo_ratings');
    }
};
