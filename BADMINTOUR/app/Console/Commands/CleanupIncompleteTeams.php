<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\TournamentCategory;
use Carbon\Carbon;

class CleanupIncompleteTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournaments:cleanup-incomplete-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove incomplete teams (missing partner) after withdrawal deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up incomplete teams...');

        $tournaments = Tournament::whereNotNull('withdrawal_deadline')
            ->where('withdrawal_deadline', '<=', now())
            ->get();

        $removedCount = 0;

        foreach ($tournaments as $tournament) {
            $categories = $tournament->categories()
                ->where(function($query) {
                    $query->whereRaw("LOWER(name) LIKE '%doubles%'")
                          ->orWhereRaw("LOWER(name) LIKE '%mixed%'");
                })
                ->get();

            foreach ($categories as $category) {
                // Find registrations without partners (incomplete teams)
                $incompleteRegistrations = TournamentRegistration::where('tournament_id', $tournament->id)
                    ->where('category_id', $category->id)
                    ->whereNull('partner_id')
                    ->whereIn('status', ['pending', 'eligible', 'approved'])
                    ->get();

                foreach ($incompleteRegistrations as $registration) {
                    $registration->update(['status' => 'withdrawn']);
                    $removedCount++;
                    
                    $this->info("Removed incomplete team: Player {$registration->player->name} in {$tournament->name} - {$category->name}");
                }
            }
        }

        $this->info("Cleanup complete. Removed {$removedCount} incomplete teams.");
        
        return Command::SUCCESS;
    }
}

