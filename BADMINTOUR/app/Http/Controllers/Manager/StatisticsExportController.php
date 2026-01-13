<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Club;
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

    public function exportManagerStatistics(Request $request, string $format = 'csv')
    {
        $manager = auth()->user();
        
        if (!$manager->isManager()) {
            abort(403, 'Unauthorized');
        }

        $stats = $this->statisticsService->getManagerStatistics($manager);

        if ($format === 'pdf') {
            return $this->exportPdf($manager, $stats);
        }

        return $this->exportCsv($manager, $stats);
    }

    public function exportClubStatistics(Request $request, string $format = 'csv')
    {
        $manager = auth()->user();
        
        if (!$manager->isManager()) {
            abort(403, 'Unauthorized');
        }

        $club = \App\Models\Club::where('manager_id', $manager->id)->first();
        
        if (!$club) {
            abort(403, 'You must have a club to export club statistics.');
        }

        $stats = $this->statisticsService->getClubStatistics($club);

        if ($format === 'pdf') {
            return $this->exportPdf($club, $stats, 'club');
        }

        return $this->exportCsv($club, $stats, 'club');
    }

    private function exportCsv($entity, $stats, $type = 'manager')
    {
        if ($type === 'club') {
            $filename = "club_statistics_{$entity->id}_" . date('Y-m-d') . '.csv';
            $name = $entity->name;
        } else {
            $filename = "manager_statistics_{$entity->id}_" . date('Y-m-d') . '.csv';
            $name = "{$entity->first_name} {$entity->last_name}";
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($entity, $stats, $type, $name) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'club') {
                fputcsv($file, ['Club Statistics Export']);
                fputcsv($file, ['Club', $name]);
            } else {
                fputcsv($file, ['Manager Statistics Export']);
                fputcsv($file, ['Manager', $name]);
            }
            fputcsv($file, ['Generated', date('Y-m-d H:i:s')]);
            fputcsv($file, []);
            
            if ($type === 'club') {
                fputcsv($file, ['Club Statistics']);
                fputcsv($file, ['Total Members', $stats['total_members']]);
                fputcsv($file, ['Active Members', $stats['active_members']]);
                fputcsv($file, ['Tournaments Hosted', $stats['tournaments_hosted']]);
                fputcsv($file, ['Total Matches', $stats['total_matches']]);
                fputcsv($file, ['Completed Matches', $stats['completed_matches']]);
                fputcsv($file, ['Average ELO Rating', $stats['average_elo']]);
                fputcsv($file, []);
                fputcsv($file, ['Tournaments by Status']);
                foreach ($stats['tournaments_by_status'] as $status => $count) {
                    fputcsv($file, [ucfirst($status), $count]);
                }
            } else {
                fputcsv($file, ['Manager Statistics']);
                fputcsv($file, ['Tournaments Organized', $stats['tournaments_organized']]);
                fputcsv($file, ['Total Participants', $stats['total_participants']]);
                fputcsv($file, ['Matches Managed', $stats['matches_managed']]);
                fputcsv($file, ['Average Tournament Size', $stats['average_tournament_size']]);
                fputcsv($file, ['Completion Rate', $stats['completion_rate'] . '%']);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportPdf($entity, $stats, $type = 'manager')
    {
        return redirect()->back()->with('error', 'PDF export not yet implemented. Please use CSV export.');
    }
}

