<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * Usage:
     * 
     * 1. FRESH DATABASE WITH TEST DATA:
     *    php artisan migrate:fresh --seed
     *    - Creates 3 clubs with verified managers
     *    - Creates 18 sample players (6 per club)
     *    - All users are verified and ready for testing
     *    - Password for all users: "password"
     * 
     * 2. EMPTY DATABASE (No Sample Data):
     *    php artisan migrate:fresh
     *    - Creates an empty database with no users or data
     *    - All pages will show "no data" empty states
     * 
     * 3. SEED ONLY (if database already exists):
     *    php artisan db:seed
     *    - Runs the TestSeeder to add test data
     */
    public function run(): void
    {
        // For production deployment, use ProductionSeeder instead
        // For development/testing, use TestSeeder
        // 
        // Production (minimal sample data):
        // php artisan db:seed --class=ProductionSeeder
        //
        // Development (full test data):
        // php artisan db:seed --class=TestSeeder
        //
        // Or run both:
        // $this->call([
        //     ProductionSeeder::class,
        //     TestSeeder::class,
        // ]);
    }
}
