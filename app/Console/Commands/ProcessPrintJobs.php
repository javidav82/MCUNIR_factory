<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;

class ProcessPrintJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending print jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting print job processing...');

        try {
            $pendingJobs = PrintJob::where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($pendingJobs as $job) {
                $this->processJob($job);
            }

            $this->info('Print job processing completed.');
        } catch (\Exception $e) {
            Log::error('Error processing print jobs: ' . $e->getMessage());
            $this->error('An error occurred while processing print jobs.');
        }
    }

    /**
     * Process a single print job
     *
     * @param PrintJob $job
     * @return void
     */
    protected function processJob(PrintJob $job)
    {
        try {
            $this->info("Processing job {$job->id}...");

            // Update job status to processing
            $job->update(['status' => 'processing']);

            // TODO: Implement actual print processing logic here
            // This is where you would integrate with your printer system

            // Simulate processing delay
            sleep(2);

            // Update job status to completed
            $job->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            $this->info("Job {$job->id} completed successfully.");
        } catch (\Exception $e) {
            Log::error("Error processing job {$job->id}: " . $e->getMessage());
            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            $this->error("Failed to process job {$job->id}.");
        }
    }
}
