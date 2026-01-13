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
        // Modify the enum to include 'draft' and 'published'
        // MySQL/MariaDB requires raw SQL to modify ENUM columns
        DB::statement("ALTER TABLE tournaments MODIFY COLUMN status ENUM('draft', 'published', 'upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE tournaments MODIFY COLUMN status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming'");
    }
};
