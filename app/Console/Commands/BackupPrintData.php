<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupPrintData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:backup {--days=30 : Number of days of data to backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup print job data and documents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting print data backup...');

        try {
            $days = $this->option('days');
            $cutoffDate = Carbon::now()->subDays($days);

            // Create backup directory with timestamp
            $backupDir = 'backups/print_data_' . Carbon::now()->format('Y-m-d_H-i-s');
            Storage::makeDirectory($backupDir);

            // Get print jobs to backup
            $jobs = PrintJob::where('created_at', '>=', $cutoffDate)
                ->get();

            $this->info("Found {$jobs->count()} print jobs to backup.");

            // Backup each job's data and document
            foreach ($jobs as $job) {
                $this->backupJob($job, $backupDir);
            }

            // Create a manifest file
            $this->createManifest($backupDir, $jobs);

            $this->info("Backup completed successfully in {$backupDir}");

        } catch (\Exception $e) {
            Log::error('Error during print data backup: ' . $e->getMessage());
            $this->error('An error occurred while backing up print data.');
        }
    }

    /**
     * Backup a specific print job
     *
     * @param PrintJob $job
     * @param string $backupDir
     * @return void
     */
    protected function backupJob(PrintJob $job, string $backupDir)
    {
        try {
            $this->info("Backing up job {$job->id}...");

            // Create job directory
            $jobDir = "{$backupDir}/job_{$job->id}";
            Storage::makeDirectory($jobDir);

            // Backup document if exists
            if (Storage::exists($job->document_path)) {
                $documentName = basename($job->document_path);
                Storage::copy(
                    $job->document_path,
                    "{$jobDir}/{$documentName}"
                );
            }

            // Create job metadata file
            $metadata = [
                'id' => $job->id,
                'document_name' => $job->document_name,
                'status' => $job->status,
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
                'user_id' => $job->user_id,
                'error_message' => $job->error_message,
            ];

            Storage::put(
                "{$jobDir}/metadata.json",
                json_encode($metadata, JSON_PRETTY_PRINT)
            );

        } catch (\Exception $e) {
            Log::error("Error backing up job {$job->id}: " . $e->getMessage());
            $this->error("Failed to backup job {$job->id}");
        }
    }

    /**
     * Create a manifest file for the backup
     *
     * @param string $backupDir
     * @param \Illuminate\Database\Eloquent\Collection $jobs
     * @return void
     */
    protected function createManifest(string $backupDir, $jobs)
    {
        $manifest = [
            'backup_date' => Carbon::now()->toIso8601String(),
            'total_jobs' => $jobs->count(),
            'jobs' => $jobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'document_name' => $job->document_name,
                    'status' => $job->status,
                    'created_at' => $job->created_at,
                ];
            })->toArray()
        ];

        Storage::put(
            "{$backupDir}/manifest.json",
            json_encode($manifest, JSON_PRETTY_PRINT)
        );
    }
}
