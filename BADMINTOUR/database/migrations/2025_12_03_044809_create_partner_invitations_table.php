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
        Schema::create('partner_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('tournament_categories')->onDelete('cascade');
            $table->foreignId('inviter_id')->constrained('users')->onDelete('cascade'); // Player sending invitation
            $table->foreignId('invitee_id')->constrained('users')->onDelete('cascade'); // Player being invited
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            // Prevent duplicate pending invitations
            $table->unique(['tournament_id', 'category_id', 'inviter_id', 'invitee_id', 'status'], 'unique_pending_invitation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_invitations');
    }
};
