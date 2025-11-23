<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds a partial unique index to ensure a player can only be approved 
     * to one club at a time, preventing race conditions.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite partial unique index
            DB::statement("
                CREATE UNIQUE INDEX idx_club_players_approved_unique 
                ON club_players(player_id) 
                WHERE status = 'approved'
            ");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL partial unique index
            DB::statement("
                CREATE UNIQUE INDEX idx_club_players_approved_unique 
                ON club_players(player_id) 
                WHERE status = 'approved'
            ");
        } else {
            // MySQL doesn't support partial indexes
            // Constraint is enforced at application level only
            // Alternative: use a trigger or generated column (more complex)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS idx_club_players_approved_unique');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_club_players_approved_unique');
        }
        // MySQL: No index to drop
    }
};
