<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrintStatusNotification;

class NotifyPrintStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for print job status changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for print job status changes...');

        try {
            // Get jobs that have changed status since last notification
            $jobs = PrintJob::where('status_changed', true)
                ->where('notified', false)
                ->get();

            foreach ($jobs as $job) {
                $this->notifyJobStatus($job);
            }

            $this->info("Processed {$jobs->count()} print job notifications.");

        } catch (\Exception $e) {
            Log::error('Error processing print job notifications: ' . $e->getMessage());
            $this->error('An error occurred while processing print job notifications.');
        }
    }

    /**
     * Send notification for a specific print job
     *
     * @param PrintJob $job
     * @return void
     */
    protected function notifyJobStatus(PrintJob $job)
    {
        try {
            $this->info("Sending notification for job {$job->id}...");

            // Get the user associated with the print job
            $user = $job->user;

            if (!$user) {
                $this->warn("No user found for job {$job->id}");
                return;
            }

            // Send email notification
            Mail::to($user->email)
                ->send(new PrintStatusNotification($job));

            // Update job notification status
            $job->update([
                'notified' => true,
                'status_changed' => false
            ]);

            $this->info("Notification sent for job {$job->id}");

        } catch (\Exception $e) {
            Log::error("Error sending notification for job {$job->id}: " . $e->getMessage());
            $this->error("Failed to send notification for job {$job->id}");
        }
    }
}
