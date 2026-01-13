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
        // Simplify: widen column, normalize values, then set final enum
        // 1) Make column flexible temporarily
        DB::statement("ALTER TABLE manager_id_verifications MODIFY id_type VARCHAR(50) NOT NULL");

        // 2) Normalize any old/unknown values to philsys_id
        $allowed = [
            'philsys_id',
            'drivers_license',
            'umid_sss',
            'philhealth',
            'tin',
            'passport',
            'voters_id',
            'postal_id',
        ];
        DB::table('manager_id_verifications')
            ->whereNotIn('id_type', $allowed)
            ->update(['id_type' => 'philsys_id']);

        // 3) Constrain to the final enum set (only new types)
        DB::statement("
            ALTER TABLE manager_id_verifications
            MODIFY id_type ENUM(
                'philsys_id',
                'drivers_license',
                'umid_sss',
                'philhealth',
                'tin',
                'passport',
                'voters_id',
                'postal_id'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Widen, normalize back, then constrain to original 3
        DB::statement("ALTER TABLE manager_id_verifications MODIFY id_type VARCHAR(50) NOT NULL");

        DB::table('manager_id_verifications')
            ->whereNotIn('id_type', ['national_id', 'drivers_license', 'passport'])
            ->update(['id_type' => 'national_id']);

        DB::statement("
            ALTER TABLE manager_id_verifications
            MODIFY id_type ENUM(
                'national_id',
                'drivers_license',
                'passport'
            ) NOT NULL
        ");
    }
};

