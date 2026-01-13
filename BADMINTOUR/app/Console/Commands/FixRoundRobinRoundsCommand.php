<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Support\Facades\DB;

class FixRoundRobinRoundsCommand extends Command
{
    protected $signature = 'tournaments:fix-round-robin-rounds {--tournament-id= : Fix specific tournament by ID} {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Fix round robin tournament round names to start from Round 1';

    public function handle()
    {
        $tournamentId = $this->option('tournament-id');
        $dryRun = $this->option('dry-run');
        
        if ($tournamentId) {
            $tournaments = Tournament::where('id', $tournamentId)
                ->where('bracket_type', 'round_robin')
                ->get();
        } else {
            $tournaments = Tournament::where('bracket_type', 'round_robin')->get();
        }

        if ($tournaments->isEmpty()) {
            $this->info('No round robin tournaments found.');
            return 0;
        }

        $this->info("Found {$tournaments->count()} round robin tournament(s).");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $totalFixed = 0;
        $totalDeleted = 0;
        
        foreach ($tournaments as $tournament) {
            $this->info("\nProcessing: {$tournament->name} (ID: {$tournament->id})");
            $result = $this->fixTournamentRounds($tournament, $dryRun);
            $totalFixed += $result['fixed'];
            $totalDeleted += $result['deleted'];
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("Summary:");
        $this->info("  Rounds fixed: {$totalFixed}");
        $this->info("  Invalid rounds deleted: {$totalDeleted}");
        
        if ($dryRun) {
            $this->warn("\nThis was a dry run. Run without --dry-run to apply changes.");
        } else {
            $this->info("\nDone! All round robin rounds have been normalized.");
        }
        
        return 0;
    }

    private function fixTournamentRounds(Tournament $tournament, bool $dryRun): array
    {
        $matchesByCategory = TournamentMatch::where('tournament_id', $tournament->id)
            ->get()
            ->groupBy('tournament_category_id');

        $totalFixed = 0;
        $totalDeleted = 0;

        foreach ($matchesByCategory as $categoryId => $matches) {
            $matchesByRound = $matches->groupBy('round');
            
            $roundNumbers = [];
            $roundRobinRounds = [];
            $eliminationRounds = [];
            
            foreach ($matchesByRound->keys() as $roundName) {
                $r = strtolower(trim((string)$roundName));
                
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    $roundNum = (int)$m[1];
                    $roundNumbers[] = $roundNum;
                    $roundRobinRounds[$roundName] = $roundNum;
                } elseif (preg_match('/round\s+of\s+\d+/i', $r) || 
                          str_contains($r, 'quarter') || 
                          str_contains($r, 'semi') || 
                          (str_contains($r, 'final') && !str_contains($r, 'semi'))) {
                    $eliminationRounds[] = $roundName;
                }
            }
            
            if (empty($roundNumbers) && empty($eliminationRounds)) {
                continue;
            }
            
            if (!empty($eliminationRounds)) {
                foreach ($eliminationRounds as $elimRound) {
                    $count = TournamentMatch::where('tournament_id', $tournament->id)
                        ->where('tournament_category_id', $categoryId)
                        ->where('round', $elimRound)
                        ->count();
                    
                    if ($count > 0) {
                        $this->line("  Category {$categoryId}: Deleting {$count} match(es) with elimination round: {$elimRound}");
                        $totalDeleted += $count;
                        
                        if (!$dryRun) {
                            TournamentMatch::where('tournament_id', $tournament->id)
                                ->where('tournament_category_id', $categoryId)
                                ->where('round', $elimRound)
                                ->delete();
                        }
                    }
                }
            }
            
            if (!empty($roundNumbers)) {
                $sortedRoundNumbers = array_unique($roundNumbers);
                sort($sortedRoundNumbers);
                
                $roundMapping = [];
                $newRoundNum = 1;
                
                foreach ($sortedRoundNumbers as $oldRoundNum) {
                    $oldRoundName = null;
                    foreach ($roundRobinRounds as $name => $num) {
                        if ($num === $oldRoundNum) {
                            $oldRoundName = $name;
                            break;
                        }
                    }
                    
                    if ($oldRoundName) {
                        $newRoundName = "Round {$newRoundNum}";
                        if ($oldRoundName !== $newRoundName) {
                            $roundMapping[$oldRoundName] = $newRoundName;
                        }
                        $newRoundNum++;
                    }
                }
                
                foreach ($roundMapping as $oldRound => $newRound) {
                    $count = TournamentMatch::where('tournament_id', $tournament->id)
                        ->where('tournament_category_id', $categoryId)
                        ->where('round', $oldRound)
                        ->count();
                    
                    if ($count > 0) {
                        $this->line("  Category {$categoryId}: Renumbering {$count} match(es) from '{$oldRound}' to '{$newRound}'");
                        $totalFixed += $count;
                        
                        if (!$dryRun) {
                            TournamentMatch::where('tournament_id', $tournament->id)
                                ->where('tournament_category_id', $categoryId)
                                ->where('round', $oldRound)
                                ->update(['round' => $newRound]);
                        }
                    }
                }
            }
        }

        return ['fixed' => $totalFixed, 'deleted' => $totalDeleted];
    }
}

