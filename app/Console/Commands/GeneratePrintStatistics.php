<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GeneratePrintStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:statistics {--period=monthly : Statistics period (daily, weekly, monthly, yearly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate print job statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting print statistics generation...');

        try {
            $period = $this->option('period');
            $startDate = $this->getStartDate($period);
            
            // Generate statistics
            $stats = $this->generateStatistics($startDate);
            
            // Save statistics
            $this->saveStatistics($stats, $period);
            
            $this->info("Print statistics generated successfully for {$period} period.");

        } catch (\Exception $e) {
            Log::error('Error generating print statistics: ' . $e->getMessage());
            $this->error('An error occurred while generating print statistics.');
        }
    }

    /**
     * Get start date based on period
     *
     * @param string $period
     * @return Carbon
     */
    protected function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'daily' => Carbon::now()->startOfDay(),
            'weekly' => Carbon::now()->startOfWeek(),
            'monthly' => Carbon::now()->startOfMonth(),
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }

    /**
     * Generate statistics for the given period
     *
     * @param Carbon $startDate
     * @return array
     */
    protected function generateStatistics(Carbon $startDate): array
    {
        $stats = [
            'total_jobs' => 0,
            'completed_jobs' => 0,
            'failed_jobs' => 0,
            'total_pages' => 0,
            'jobs_by_status' => [],
            'jobs_by_printer' => [],
            'jobs_by_user' => [],
            'average_jobs_per_day' => 0,
            'busiest_day' => null,
            'busiest_day_count' => 0,
        ];

        // Get jobs for the period
        $jobs = PrintJob::where('created_at', '>=', $startDate)->get();

        if ($jobs->isEmpty()) {
            return $stats;
        }

        // Calculate basic statistics
        $stats['total_jobs'] = $jobs->count();
        $stats['completed_jobs'] = $jobs->where('status', 'completed')->count();
        $stats['failed_jobs'] = $jobs->where('status', 'failed')->count();
        $stats['total_pages'] = $jobs->sum('pages_printed');

        // Group by status
        $stats['jobs_by_status'] = $jobs->groupBy('status')
            ->map(fn($group) => $group->count())
            ->toArray();

        // Group by printer
        $stats['jobs_by_printer'] = $jobs->groupBy('printer_id')
            ->map(fn($group) => [
                'count' => $group->count(),
                'pages' => $group->sum('pages_printed')
            ])
            ->toArray();

        // Group by user
        $stats['jobs_by_user'] = $jobs->groupBy('user_id')
            ->map(fn($group) => [
                'count' => $group->count(),
                'pages' => $group->sum('pages_printed')
            ])
            ->toArray();

        // Calculate daily statistics
        $dailyStats = $jobs->groupBy(fn($job) => $job->created_at->format('Y-m-d'))
            ->map(fn($group) => $group->count());

        $stats['average_jobs_per_day'] = $dailyStats->avg();
        $stats['busiest_day'] = $dailyStats->sortDesc()->keys()->first();
        $stats['busiest_day_count'] = $dailyStats->max();

        return $stats;
    }

    /**
     * Save statistics to file
     *
     * @param array $stats
     * @param string $period
     * @return void
     */
    protected function saveStatistics(array $stats, string $period): void
    {
        try {
            $filename = "statistics/print_stats_{$period}_" . Carbon::now()->format('Y-m-d_H-i-s') . '.json';
            
            $data = [
                'period' => $period,
                'generated_at' => Carbon::now()->toIso8601String(),
                'statistics' => $stats
            ];

            Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT));
            
            $this->info("Statistics saved to {$filename}");

        } catch (\Exception $e) {
            Log::error('Error saving print statistics: ' . $e->getMessage());
            $this->error('Failed to save print statistics');
        }
    }
}
