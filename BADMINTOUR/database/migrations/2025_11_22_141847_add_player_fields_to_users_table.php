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
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('contact_number')->nullable()->after('email');
            $table->integer('birth_month')->nullable()->after('contact_number');
            $table->integer('birth_day')->nullable()->after('birth_month');
            $table->integer('birth_year')->nullable()->after('birth_day');
            $table->string('gender')->nullable()->after('birth_year');
            $table->decimal('height', 5, 2)->nullable()->after('gender');
            $table->decimal('weight', 5, 2)->nullable()->after('height');
            $table->string('region')->nullable()->after('weight');
            $table->string('province')->nullable()->after('region');
            $table->string('city')->nullable()->after('province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'contact_number',
                'birth_month',
                'birth_day',
                'birth_year',
                'gender',
                'height',
                'weight',
                'region',
                'province',
                'city'
            ]);
        });
    }
};
