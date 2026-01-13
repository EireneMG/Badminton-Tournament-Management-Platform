<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class StatisticsExportController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function exportPlayerStatistics(Request $request, string $format = 'csv')
    {
        $player = auth()->user();
        
        if (!$player->isPlayer()) {
            abort(403, 'Unauthorized');
        }

        $stats = $this->statisticsService->getPlayerStatistics($player);

        if ($format === 'pdf') {
            return $this->exportPdf($player, $stats);
        }

        return $this->exportCsv($player, $stats);
    }

    private function exportCsv($player, $stats)
    {
        $filename = "player_statistics_{$player->id}_" . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($player, $stats) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['Player Statistics Export']);
            fputcsv($file, ['Player', "{$player->first_name} {$player->last_name}"]);
            fputcsv($file, ['Generated', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            fputcsv($file, ['Overall Statistics']);
            fputcsv($file, ['Total Matches', $stats['total_matches']]);
            fputcsv($file, ['Wins', $stats['wins']]);
            fputcsv($file, ['Losses', $stats['losses']]);
            fputcsv($file, ['Win Rate', $stats['win_rate'] . '%']);
            fputcsv($file, ['Average Points per Set', $stats['average_points']]);
            fputcsv($file, []);
            
            fputcsv($file, ['Recent Performance (Last 10 Matches)']);
            fputcsv($file, ['Recent Wins', $stats['recent_wins']]);
            fputcsv($file, ['Recent Losses', $stats['recent_losses']]);
            fputcsv($file, ['Recent Win Rate', $stats['recent_win_rate'] . '%']);
            fputcsv($file, []);
            
            fputcsv($file, ['Tournament Statistics']);
            fputcsv($file, ['Tournaments Joined', $stats['tournament_stats']['tournaments_joined']]);
            fputcsv($file, ['Tournaments Completed', $stats['tournament_stats']['tournaments_completed']]);
            fputcsv($file, ['Best Finish', $stats['tournament_stats']['best_finish']]);
            fputcsv($file, []);
            
            fputcsv($file, ['Category Statistics']);
            fputcsv($file, ['Category', 'Matches', 'Wins', 'Losses', 'Win Rate']);
            foreach ($stats['category_stats'] as $category => $catStats) {
                if ($catStats['matches'] > 0) {
                    fputcsv($file, [
                        $category,
                        $catStats['matches'],
                        $catStats['wins'],
                        $catStats['losses'],
                        $catStats['win_rate'] . '%'
                    ]);
                }
            }
            fputcsv($file, []);
            
            fputcsv($file, ['ELO Ratings']);
            fputcsv($file, ['Category', 'Current Rating', 'Peak Rating', 'Matches Played']);
            foreach ($stats['elo_ratings'] as $elo) {
                fputcsv($file, [
                    $elo->category,
                    $elo->current_rating,
                    $elo->peak_rating ?? $elo->current_rating,
                    $elo->matches_played ?? 0
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportPdf($player, $stats)
    {
        return redirect()->back()->with('error', 'PDF export not yet implemented. Please use CSV export.');
    }
}

