<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GeneratePrintReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:reports {--period=daily : Report period (daily, weekly, monthly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate print job reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $this->info("Generating {$period} print reports...");

        try {
            $reportData = $this->generateReportData($period);
            $filename = $this->saveReport($reportData, $period);

            $this->info("Report generated successfully: {$filename}");

        } catch (\Exception $e) {
            Log::error('Error generating print reports: ' . $e->getMessage());
            $this->error('An error occurred while generating print reports.');
        }
    }

    /**
     * Generate report data based on the specified period
     *
     * @param string $period
     * @return array
     */
    protected function generateReportData(string $period): array
    {
        $startDate = match($period) {
            'daily' => Carbon::now()->startOfDay(),
            'weekly' => Carbon::now()->startOfWeek(),
            'monthly' => Carbon::now()->startOfMonth(),
            default => Carbon::now()->startOfDay()
        };

        $endDate = Carbon::now();

        $jobs = PrintJob::whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $stats = [
            'total_jobs' => $jobs->count(),
            'completed_jobs' => $jobs->where('status', 'completed')->count(),
            'failed_jobs' => $jobs->where('status', 'failed')->count(),
            'pending_jobs' => $jobs->where('status', 'pending')->count(),
            'processing_jobs' => $jobs->where('status', 'processing')->count(),
            'total_pages' => $jobs->sum('page_count'),
            'period' => $period,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString()
        ];

        return $stats;
    }

    /**
     * Save the report to a file
     *
     * @param array $data
     * @param string $period
     * @return string
     */
    protected function saveReport(array $data, string $period): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "print_reports/{$period}/report_{$timestamp}.json";

        Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT));

        return $filename;
    }
}
