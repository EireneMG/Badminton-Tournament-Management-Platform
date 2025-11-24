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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('tournament_categories')->onDelete('cascade');
            $table->foreignId('player1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('round');
            $table->integer('bracket_position')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('rescheduled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
