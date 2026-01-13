<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'draft' back to the enum
        // MySQL/MariaDB requires raw SQL to modify ENUM columns
        DB::statement("ALTER TABLE tournaments MODIFY COLUMN status ENUM('draft', 'published', 'upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'draft' from enum again
        DB::statement("ALTER TABLE tournaments MODIFY COLUMN status ENUM('published', 'upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'published'");
    }
};

