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
        // First, update any existing draft tournaments to 'published' status
        DB::table('tournaments')
            ->where('status', 'draft')
            ->update(['status' => 'published']);
        
        // Remove 'draft' from the enum
        // MySQL/MariaDB requires raw SQL to modify ENUM columns
        DB::statement("ALTER TABLE tournaments MODIFY COLUMN status ENUM('published', 'upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'published'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to include 'draft' in enum
        DB::statement("ALTER TABLE tournaments MODIFY COLUMN status ENUM('draft', 'published', 'upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'draft'");
    }
};
