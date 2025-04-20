<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ValidateDocumentIssuesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_access_validate_document_issues_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/validate/document-issues');

        $response->assertStatus(200)
            ->assertViewIs('validate.document-issues')
            ->assertSee('Validate Document Issues');
    }

    /** @test */
    public function it_can_load_document_issues_list()
    {
        $issues = DocumentIssue::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/api/document-issues');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'issues' => [
                        '*' => [
                            'id',
                            'job_id',
                            'title',
                            'issue_type',
                            'status',
                            'priority',
                            'reported_at',
                            'updated_at'
                        ]
                    ],
                    'pagination'
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_document_issues()
    {
        $pendingIssue = DocumentIssue::factory()->create(['status' => 'pending']);
        $resolvedIssue = DocumentIssue::factory()->create(['status' => 'resolved']);

        $response = $this->actingAs($this->user)
            ->get('/api/document-issues?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.issues')
            ->assertJsonFragment(['id' => $pendingIssue->id])
            ->assertJsonMissing(['id' => $resolvedIssue->id]);
    }

    /** @test */
    public function it_can_view_document_issue_details()
    {
        $issue = DocumentIssue::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/api/document-issues/{$issue->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'job_id',
                    'title',
                    'issue_type',
                    'status',
                    'priority',
                    'description',
                    'document_type',
                    'pages',
                    'format',
                    'reported_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function it_can_load_new_document_issue()
    {
        Storage::fake('documents');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post('/api/document-issues/load', [
                'job_id' => 'JOB123',
                'title' => 'Test Issue',
                'issue_type' => 'format',
                'priority' => 'high',
                'document_type' => 'PDF',
                'pages' => 10,
                'format' => 'A4',
                'description' => 'Test description',
                'document_file' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Document issue loaded successfully']);

        $this->assertDatabaseHas('document_issues', [
            'job_id' => 'JOB123',
            'title' => 'Test Issue',
            'issue_type' => 'format',
            'priority' => 'high'
        ]);

        Storage::disk('documents')->assertExists($file->hashName());
    }

    /** @test */
    public function it_validates_required_fields_when_loading_document_issue()
    {
        $response = $this->actingAs($this->user)
            ->post('/api/document-issues/load', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'job_id',
                'title',
                'issue_type',
                'priority',
                'document_type',
                'pages',
                'format',
                'description',
                'document_file'
            ]);
    }

    /** @test */
    public function it_validates_document_file_type()
    {
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->actingAs($this->user)
            ->post('/api/document-issues/load', [
                'job_id' => 'JOB123',
                'title' => 'Test Issue',
                'issue_type' => 'format',
                'priority' => 'high',
                'document_type' => 'PDF',
                'pages' => 10,
                'format' => 'A4',
                'description' => 'Test description',
                'document_file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document_file']);
    }

    /** @test */
    public function it_can_validate_document_issues()
    {
        $issues = DocumentIssue::factory()->count(2)->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->post('/api/document-issues/validate', [
                'issue_ids' => $issues->pluck('id')->toArray(),
                'status' => 'resolved',
                'resolution_notes' => 'Issues resolved',
                'action_taken' => 'fixed',
                'additional_comments' => 'No further action needed'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Issues validated successfully']);

        foreach ($issues as $issue) {
            $this->assertDatabaseHas('document_issues', [
                'id' => $issue->id,
                'status' => 'resolved'
            ]);
        }
    }

    /** @test */
    public function it_validates_required_fields_when_validating_issues()
    {
        $response = $this->actingAs($this->user)
            ->post('/api/document-issues/validate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'issue_ids',
                'status',
                'resolution_notes',
                'action_taken'
            ]);
    }
} 