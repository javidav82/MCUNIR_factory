<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupPrintJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:cleanup {--days=30 : Number of days to keep completed jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old completed print jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up print jobs older than {$days} days...");

        try {
            $deletedCount = PrintJob::where('status', 'completed')
                ->where('completed_at', '<', $cutoffDate)
                ->delete();

            $this->info("Successfully deleted {$deletedCount} old print jobs.");

            // Also clean up failed jobs that are older than the cutoff date
            $failedCount = PrintJob::where('status', 'failed')
                ->where('updated_at', '<', $cutoffDate)
                ->delete();

            $this->info("Successfully deleted {$failedCount} old failed print jobs.");

        } catch (\Exception $e) {
            Log::error('Error cleaning up print jobs: ' . $e->getMessage());
            $this->error('An error occurred while cleaning up print jobs.');
        }
    }
}
