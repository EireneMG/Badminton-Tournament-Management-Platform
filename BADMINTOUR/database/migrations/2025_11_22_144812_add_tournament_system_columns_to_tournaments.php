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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('banner_path')->nullable()->after('description');
            $table->string('venue_name')->after('club_id');
            $table->integer('number_of_courts')->default(1)->after('venue_name');
            $table->string('contact_email')->after('number_of_courts');
            $table->string('contact_phone')->after('contact_email');
            $table->decimal('tournament_fee', 10, 2)->default(0)->after('contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['banner_path', 'venue_name', 'number_of_courts', 'contact_email', 'contact_phone', 'tournament_fee']);
        });
    }
};
