<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PrintQueueAlert;

class MonitorPrintQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:monitor-queue {--threshold=10 : Alert threshold for pending jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor print queue and send alerts if needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting print queue monitoring...');

        try {
            $threshold = $this->option('threshold');
            
            // Get pending jobs count
            $pendingJobs = PrintJob::where('status', 'pending')->count();
            $this->info("Current pending jobs: {$pendingJobs}");

            // Check if threshold is exceeded
            if ($pendingJobs >= $threshold) {
                $this->sendQueueAlert($pendingJobs, $threshold);
            }

            // Check for stuck jobs
            $this->checkStuckJobs();

            $this->info('Print queue monitoring completed.');

        } catch (\Exception $e) {
            Log::error('Error during print queue monitoring: ' . $e->getMessage());
            $this->error('An error occurred while monitoring the print queue.');
        }
    }

    /**
     * Send alert notification for high queue
     *
     * @param int $pendingJobs
     * @param int $threshold
     * @return void
     */
    protected function sendQueueAlert(int $pendingJobs, int $threshold)
    {
        try {
            $this->warn("Queue threshold exceeded: {$pendingJobs} jobs pending (threshold: {$threshold})");

            // Get administrators to notify
            $admins = \App\Models\User::where('is_admin', true)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new PrintQueueAlert($pendingJobs, $threshold));
                $this->info('Alert notification sent to administrators.');
            } else {
                $this->warn('No administrators found to notify.');
            }

        } catch (\Exception $e) {
            Log::error('Error sending queue alert: ' . $e->getMessage());
            $this->error('Failed to send queue alert notification');
        }
    }

    /**
     * Check for stuck jobs
     *
     * @return void
     */
    protected function checkStuckJobs()
    {
        try {
            // Find jobs that have been in processing state for too long
            $stuckTime = now()->subMinutes(30); // 30 minutes threshold
            $stuckJobs = PrintJob::where('status', 'processing')
                ->where('updated_at', '<', $stuckTime)
                ->get();

            if ($stuckJobs->isNotEmpty()) {
                $this->warn("Found {$stuckJobs->count()} stuck jobs.");

                foreach ($stuckJobs as $job) {
                    $this->handleStuckJob($job);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error checking stuck jobs: ' . $e->getMessage());
            $this->error('Failed to check for stuck jobs');
        }
    }

    /**
     * Handle a stuck print job
     *
     * @param PrintJob $job
     * @return void
     */
    protected function handleStuckJob(PrintJob $job)
    {
        try {
            $this->info("Handling stuck job {$job->id}...");

            // Update job status
            $job->update([
                'status' => 'failed',
                'error_message' => 'Job stuck in processing state for too long'
            ]);

            // Notify user
            if ($job->user) {
                $job->user->notify(new PrintQueueAlert(
                    "Your print job '{$job->document_name}' has failed due to being stuck in processing state.",
                    'stuck_job'
                ));
            }

            $this->info("Stuck job {$job->id} handled successfully.");

        } catch (\Exception $e) {
            Log::error("Error handling stuck job {$job->id}: " . $e->getMessage());
            $this->error("Failed to handle stuck job {$job->id}");
        }
    }
}
