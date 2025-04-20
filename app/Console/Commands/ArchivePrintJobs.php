<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ArchivePrintJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:archive {--days=90 : Archive jobs older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive completed print jobs older than specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting print job archiving...');

        try {
            $days = $this->option('days');
            $cutoffDate = Carbon::now()->subDays($days);

            // Get completed jobs older than cutoff date
            $jobs = PrintJob::where('status', 'completed')
                ->where('created_at', '<', $cutoffDate)
                ->get();

            $this->info("Found {$jobs->count()} jobs to archive.");

            // Create archive directory with timestamp
            $archiveDir = 'archives/print_jobs_' . Carbon::now()->format('Y-m-d_H-i-s');
            Storage::makeDirectory($archiveDir);

            // Archive each job
            foreach ($jobs as $job) {
                $this->archiveJob($job, $archiveDir);
            }

            // Create manifest file
            $this->createArchiveManifest($archiveDir, $jobs);

            $this->info("Archiving completed successfully in {$archiveDir}");

        } catch (\Exception $e) {
            Log::error('Error during print job archiving: ' . $e->getMessage());
            $this->error('An error occurred while archiving print jobs.');
        }
    }

    /**
     * Archive a specific print job
     *
     * @param PrintJob $job
     * @param string $archiveDir
     * @return void
     */
    protected function archiveJob(PrintJob $job, string $archiveDir)
    {
        try {
            $this->info("Archiving job {$job->id}...");

            // Create job directory in archive
            $jobDir = "{$archiveDir}/job_{$job->id}";
            Storage::makeDirectory($jobDir);

            // Archive document if exists
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
                'completed_at' => $job->updated_at,
                'user_id' => $job->user_id,
                'pages_printed' => $job->pages_printed,
                'printer_id' => $job->printer_id,
            ];

            Storage::put(
                "{$jobDir}/metadata.json",
                json_encode($metadata, JSON_PRETTY_PRINT)
            );

            // Delete original job data
            $job->delete();

            $this->info("Job {$job->id} archived successfully.");

        } catch (\Exception $e) {
            Log::error("Error archiving job {$job->id}: " . $e->getMessage());
            $this->error("Failed to archive job {$job->id}");
        }
    }

    /**
     * Create a manifest file for the archive
     *
     * @param string $archiveDir
     * @param \Illuminate\Database\Eloquent\Collection $jobs
     * @return void
     */
    protected function createArchiveManifest(string $archiveDir, $jobs)
    {
        $manifest = [
            'archive_date' => Carbon::now()->toIso8601String(),
            'total_jobs' => $jobs->count(),
            'jobs' => $jobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'document_name' => $job->document_name,
                    'created_at' => $job->created_at,
                    'completed_at' => $job->updated_at,
                    'user_id' => $job->user_id,
                    'pages_printed' => $job->pages_printed,
                ];
            })->toArray()
        ];

        Storage::put(
            "{$archiveDir}/manifest.json",
            json_encode($manifest, JSON_PRETTY_PRINT)
        );
    }
}
