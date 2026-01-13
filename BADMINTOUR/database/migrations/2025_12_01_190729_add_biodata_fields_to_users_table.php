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
            $table->string('school_status')->nullable()->after('city');
            $table->string('school_name')->nullable()->after('school_status');
            $table->json('badminton_history')->nullable()->after('school_name');
            $table->string('years_of_experience')->nullable()->after('badminton_history');
            $table->string('experience_level')->nullable()->after('years_of_experience');
            $table->string('competitive_background')->nullable()->after('experience_level');
            $table->string('profile_photo')->nullable()->after('competitive_background');
            $table->string('player_id_document')->nullable()->after('profile_photo');
            $table->string('id_type')->nullable()->after('player_id_document');
            $table->boolean('biodata_completed')->default(false)->after('id_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'school_status',
                'school_name',
                'badminton_history',
                'years_of_experience',
                'experience_level',
                'competitive_background',
                'profile_photo',
                'player_id_document',
                'id_type',
                'biodata_completed'
            ]);
        });
    }
};
