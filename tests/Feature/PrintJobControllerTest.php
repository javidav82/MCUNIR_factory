<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PrintJob;
use App\Models\PrintDocument;
use App\Models\PrintDocumentIssue;
use App\Models\PrintStatusTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class PrintJobControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_list_user_print_jobs()
    {
        // Create print jobs for the user
        $printJobs = PrintJob::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        // Create print jobs for another user
        PrintJob::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/user/print-jobs');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'jobs' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'status',
                                'created_at',
                                'updated_at',
                                'documents',
                                'status_history'
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total'
                        ]
                    ]
                ])
                ->assertJsonCount(3, 'data.jobs');
    }

    /** @test */
    public function it_can_get_specific_print_job_details()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/user/print-jobs/{$printJob->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'created_at',
                        'updated_at',
                        'documents',
                        'status_history'
                    ]
                ]);
    }

    /** @test */
    public function it_cannot_access_other_users_print_jobs()
    {
        $otherUser = User::factory()->create();
        $printJob = PrintJob::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/user/print-jobs/{$printJob->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_print_job_status()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/user/print-jobs/{$printJob->id}/status", [
            'status' => 'completed',
            'comments' => 'Job completed successfully'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'status',
                        'status_history'
                    ]
                ]);

        $this->assertDatabaseHas('print_jobs', [
            'id' => $printJob->id,
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('print_status_tracking', [
            'print_job_id' => $printJob->id,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function it_can_validate_document()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/user/documents/{$document->id}/validate", [
            'is_valid' => true,
            'comments' => 'Document looks good',
            'issues' => []
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'status',
                        'validated_by',
                        'validated_at',
                        'issues',
                        'status_history'
                    ]
                ]);

        $this->assertDatabaseHas('print_documents', [
            'id' => $document->id,
            'status' => 'validated'
        ]);
    }

    /** @test */
    public function it_can_report_document_issues()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/user/documents/{$document->id}/validate", [
            'is_valid' => false,
            'comments' => 'Document has issues',
            'issues' => [
                [
                    'description' => 'Missing page numbers',
                    'severity' => 'medium'
                ],
                [
                    'description' => 'Incorrect formatting',
                    'severity' => 'high'
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('print_documents', [
            'id' => $document->id,
            'status' => 'invalid'
        ]);

        $this->assertDatabaseCount('print_document_issues', 2);
    }

    /** @test */
    public function it_can_manage_document_issues()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $issue = PrintDocumentIssue::factory()->create([
            'print_document_id' => $document->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/user/documents/{$document->id}/issues", [
            'issue' => 'Updated issue description',
            'status' => 'resolved',
            'comments' => 'Issue has been fixed'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'issue',
                        'document' => [
                            'id',
                            'status',
                            'status_history',
                            'issues'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('print_document_issues', [
            'id' => $issue->id,
            'status' => 'resolved'
        ]);
    }

    /** @test */
    public function it_can_get_document_issues()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $issues = PrintDocumentIssue::factory()->count(3)->create([
            'print_document_id' => $document->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/user/documents/{$document->id}/issues");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'issues',
                        'status_history'
                    ]
                ])
                ->assertJsonCount(3, 'data.issues');
    }

    /** @test */
    public function it_validates_required_fields_for_document_validation()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/user/documents/{$document->id}/validate", [
            'is_valid' => true
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors'
                ]);
    }

    /** @test */
    public function it_validates_required_fields_for_issue_management()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/user/documents/{$document->id}/issues", [
            'status' => 'resolved'
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors'
                ]);
    }

    /** @test */
    public function it_can_filter_print_jobs_by_status()
    {
        // Create print jobs with different statuses
        PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);
        PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);
        PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'failed'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/user/print-jobs?status=completed');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data.jobs')
                ->assertJsonPath('data.jobs.0.status', 'completed');
    }

    /** @test */
    public function it_can_paginate_print_jobs()
    {
        // Create 15 print jobs
        PrintJob::factory()->count(15)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/user/print-jobs?page=2&per_page=5');

        $response->assertStatus(200)
                ->assertJsonPath('data.pagination.current_page', 2)
                ->assertJsonPath('data.pagination.per_page', 5)
                ->assertJsonPath('data.pagination.total', 15)
                ->assertJsonCount(5, 'data.jobs');
    }

    /** @test */
    public function it_can_sort_print_jobs_by_creation_date()
    {
        // Create print jobs with specific creation dates
        $oldest = PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2)
        ]);
        $newest = PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()
        ]);
        $middle = PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDay()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/user/print-jobs');

        $response->assertStatus(200)
                ->assertJsonPath('data.jobs.0.id', $newest->id)
                ->assertJsonPath('data.jobs.1.id', $middle->id)
                ->assertJsonPath('data.jobs.2.id', $oldest->id);
    }

    /** @test */
    public function it_can_handle_document_validation_with_multiple_issues()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $issues = [
            [
                'description' => 'Missing page numbers',
                'severity' => 'medium'
            ],
            [
                'description' => 'Incorrect formatting',
                'severity' => 'high'
            ],
            [
                'description' => 'Low resolution images',
                'severity' => 'low'
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/user/documents/{$document->id}/validate", [
            'is_valid' => false,
            'comments' => 'Multiple issues found',
            'issues' => $issues
        ]);

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data.issues');

        foreach ($issues as $issue) {
            $this->assertDatabaseHas('print_document_issues', [
                'print_document_id' => $document->id,
                'issue' => $issue['description'],
                'severity' => $issue['severity']
            ]);
        }
    }

    /** @test */
    public function it_can_update_issue_status_with_resolution_details()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        $issue = PrintDocumentIssue::factory()->create([
            'print_document_id' => $document->id,
            'status' => 'pending'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/user/documents/{$document->id}/issues", [
            'issue' => 'Updated issue description',
            'status' => 'resolved',
            'comments' => 'Issue has been fixed by updating the document format',
            'resolution_details' => 'Converted to PDF/A format'
        ]);

        $response->assertStatus(200)
                ->assertJsonPath('data.issue.status', 'resolved');

        $this->assertDatabaseHas('print_document_issues', [
            'id' => $issue->id,
            'status' => 'resolved',
            'resolved_by' => $this->user->id,
            'resolved_at' => now()
        ]);
    }

    /** @test */
    public function it_can_handle_concurrent_status_updates()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Simulate concurrent updates
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->putJson("/api/user/print-jobs/{$printJob->id}/status", [
                'status' => 'processing',
                'comments' => "Update attempt {$i}"
            ]);
        }

        // All updates should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Verify status history has all updates
        $this->assertDatabaseCount('print_status_tracking', 3);
    }

    /** @test */
    public function it_can_handle_document_validation_with_attachments()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $document = PrintDocument::factory()->create([
            'print_job_id' => $printJob->id
        ]);

        // Create a test file
        Storage::fake('public');
        $file = UploadedFile::fake()->create('validation_report.pdf', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/user/documents/{$document->id}/validate", [
            'is_valid' => true,
            'comments' => 'Document validated with report',
            'attachments' => [$file]
        ]);

        $response->assertStatus(200);

        // Verify file was stored
        Storage::disk('public')->assertExists('validation_reports/' . $file->hashName());
    }

    /** @test */
    public function it_can_handle_bulk_document_validation()
    {
        $printJob = PrintJob::factory()->create([
            'user_id' => $this->user->id
        ]);

        $documents = PrintDocument::factory()->count(3)->create([
            'print_job_id' => $printJob->id
        ]);

        $validations = [];
        foreach ($documents as $document) {
            $validations[] = [
                'document_id' => $document->id,
                'is_valid' => true,
                'comments' => "Document {$document->id} validated"
            ];
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/user/documents/bulk-validate', [
            'validations' => $validations
        ]);

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');

        foreach ($documents as $document) {
            $this->assertDatabaseHas('print_documents', [
                'id' => $document->id,
                'status' => 'validated'
            ]);
        }
    }
} 