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
        Schema::table('users', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('users', 'years_of_experience')) {
                $table->string('years_of_experience')->nullable()->after('badminton_history');
            }
            if (!Schema::hasColumn('users', 'experience_level')) {
                $table->string('experience_level')->nullable()->after('years_of_experience');
            }
            if (!Schema::hasColumn('users', 'competitive_background')) {
                $table->string('competitive_background')->nullable()->after('experience_level');
            }
            if (!Schema::hasColumn('users', 'id_type')) {
                $table->string('id_type')->nullable()->after('player_id_document');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'years_of_experience')) {
                $table->dropColumn('years_of_experience');
            }
            if (Schema::hasColumn('users', 'experience_level')) {
                $table->dropColumn('experience_level');
            }
            if (Schema::hasColumn('users', 'competitive_background')) {
                $table->dropColumn('competitive_background');
            }
            if (Schema::hasColumn('users', 'id_type')) {
                $table->dropColumn('id_type');
            }
        });
    }
};
