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
            $table->foreignId('winner_partner_id')
                ->nullable()
                ->after('winner_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_partner_id');
        });
    }
};

