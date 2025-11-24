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
        Schema::create('tournament_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('tournament_categories')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending_payment', 'paid', 'approved', 'rejected', 'withdrawn'])->default('pending_payment');
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->timestamps();
            
            $table->unique(['tournament_id', 'player_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_registrations');
    }
};
