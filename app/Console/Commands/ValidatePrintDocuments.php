<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ValidatePrintDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:validate-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate print job documents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting document validation...');

        try {
            // Get all pending print jobs
            $jobs = PrintJob::where('status', 'pending')
                ->get();

            foreach ($jobs as $job) {
                $this->validateDocument($job);
            }

            $this->info("Validated {$jobs->count()} print jobs.");

        } catch (\Exception $e) {
            Log::error('Error validating print job documents: ' . $e->getMessage());
            $this->error('An error occurred while validating print job documents.');
        }
    }

    /**
     * Validate a specific print job document
     *
     * @param PrintJob $job
     * @return void
     */
    protected function validateDocument(PrintJob $job)
    {
        try {
            $this->info("Validating document for job {$job->id}...");

            // Check if document exists in storage
            if (!Storage::exists($job->document_path)) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Document file not found'
                ]);
                $this->warn("Document not found for job {$job->id}");
                return;
            }

            // Get document size
            $size = Storage::size($job->document_path);

            // Validate document size (max 10MB)
            if ($size > 10 * 1024 * 1024) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Document size exceeds 10MB limit'
                ]);
                $this->warn("Document too large for job {$job->id}");
                return;
            }

            // Validate document type
            $extension = pathinfo($job->document_path, PATHINFO_EXTENSION);
            $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
            
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Invalid document type'
                ]);
                $this->warn("Invalid document type for job {$job->id}");
                return;
            }

            // If all validations pass, mark as validated
            $job->update([
                'status' => 'validated'
            ]);

            $this->info("Document validated for job {$job->id}");

        } catch (\Exception $e) {
            Log::error("Error validating document for job {$job->id}: " . $e->getMessage());
            $this->error("Failed to validate document for job {$job->id}");
        }
    }
}
