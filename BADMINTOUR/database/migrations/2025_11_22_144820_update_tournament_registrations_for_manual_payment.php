<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                CREATE TABLE tournament_registrations_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tournament_id INTEGER NOT NULL,
                    player_id INTEGER NOT NULL,
                    category_id INTEGER,
                    partner_id INTEGER,
                    status TEXT CHECK(status IN ('pending', 'eligible', 'awaiting_payment', 'paid', 'approved', 'rejected', 'withdrawn')) DEFAULT 'pending',
                    payment_verified_at DATETIME,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY(tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
                    FOREIGN KEY(player_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY(category_id) REFERENCES tournament_categories(id) ON DELETE SET NULL,
                    FOREIGN KEY(partner_id) REFERENCES users(id) ON DELETE SET NULL,
                    UNIQUE(tournament_id, player_id, category_id)
                )
            ");
            
            DB::statement("
                INSERT INTO tournament_registrations_new (id, tournament_id, player_id, category_id, partner_id, status, payment_verified_at, created_at, updated_at)
                SELECT id, tournament_id, player_id, category_id, partner_id, 
                    CASE 
                        WHEN status = 'pending_payment' THEN 'awaiting_payment'
                        ELSE status
                    END,
                    payment_verified_at, created_at, updated_at
                FROM tournament_registrations
            ");
            
            DB::statement("DROP TABLE tournament_registrations");
            DB::statement("ALTER TABLE tournament_registrations_new RENAME TO tournament_registrations");
        } else {
            Schema::table('tournament_registrations', function (Blueprint $table) {
                $table->dropColumn('payment_proof');
            });
            
            DB::statement("ALTER TABLE tournament_registrations MODIFY COLUMN status ENUM('pending', 'eligible', 'awaiting_payment', 'paid', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_registrations', function (Blueprint $table) {
            //
        });
    }
};
