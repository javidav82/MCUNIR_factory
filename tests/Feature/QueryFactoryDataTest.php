<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PrintJob;
use App\Models\FactoryStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueryFactoryDataTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_access_query_factory_data_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/query/info-factory');

        $response->assertStatus(200)
            ->assertViewIs('query.info-factory')
            ->assertSee('Query Info Factory');
    }

    /** @test */
    public function it_can_load_factory_status()
    {
        $factoryStatus = FactoryStatus::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('/api/factory/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'total_jobs',
                        'completed_jobs',
                        'pending_jobs',
                        'failed_jobs'
                    ],
                    'details' => [
                        'name',
                        'status',
                        'last_maintenance',
                        'next_maintenance'
                    ],
                    'activities' => [
                        '*' => [
                            'title',
                            'description',
                            'timestamp'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_load_factory_performance_data()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/factory/performance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'labels',
                    'completed_jobs',
                    'failed_jobs'
                ]
            ]);
    }

    /** @test */
    public function it_can_load_factory_jobs()
    {
        $jobs = PrintJob::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/api/factory/jobs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'jobs' => [
                        '*' => [
                            'id',
                            'title',
                            'job_status',
                            'document_status',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'pagination'
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_factory_jobs_by_status()
    {
        $pendingJob = PrintJob::factory()->create(['job_status' => 'pending']);
        $completedJob = PrintJob::factory()->create(['job_status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->get('/api/factory/jobs?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.jobs')
            ->assertJsonFragment(['id' => $pendingJob->id])
            ->assertJsonMissing(['id' => $completedJob->id]);
    }

    /** @test */
    public function it_can_update_job_status()
    {
        $job = PrintJob::factory()->create(['job_status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->post('/api/factory/jobs/update-status', [
                'job_ids' => [$job->id],
                'status' => 'processing',
                'notes' => 'Job is being processed'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Status updated successfully']);

        $this->assertDatabaseHas('print_jobs', [
            'id' => $job->id,
            'job_status' => 'processing'
        ]);
    }

    /** @test */
    public function it_can_update_multiple_job_statuses()
    {
        $jobs = PrintJob::factory()->count(2)->create(['job_status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->post('/api/factory/jobs/update-status', [
                'job_ids' => $jobs->pluck('id')->toArray(),
                'status' => 'completed',
                'notes' => 'Jobs completed successfully'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Status updated successfully']);

        foreach ($jobs as $job) {
            $this->assertDatabaseHas('print_jobs', [
                'id' => $job->id,
                'job_status' => 'completed'
            ]);
        }
    }

    /** @test */
    public function it_validates_required_fields_when_updating_job_status()
    {
        $response = $this->actingAs($this->user)
            ->post('/api/factory/jobs/update-status', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'job_ids',
                'status'
            ]);
    }

    /** @test */
    public function it_can_view_job_details()
    {
        $job = PrintJob::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/api/factory/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'job_status',
                    'document_status',
                    'document_type',
                    'pages',
                    'format',
                    'last_validation',
                    'created_at',
                    'updated_at',
                    'feedback'
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get('/query/info-factory');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_search_factory_jobs()
    {
        $job1 = PrintJob::factory()->create(['title' => 'Test Job 1']);
        $job2 = PrintJob::factory()->create(['title' => 'Another Job']);

        $response = $this->actingAs($this->user)
            ->get('/api/factory/jobs?search=Test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.jobs')
            ->assertJsonFragment(['id' => $job1->id])
            ->assertJsonMissing(['id' => $job2->id]);
    }

    /** @test */
    public function it_can_filter_factory_jobs_by_date_range()
    {
        $oldJob = PrintJob::factory()->create(['created_at' => now()->subMonths(2)]);
        $recentJob = PrintJob::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->get('/api/factory/jobs?date_range=' . now()->subMonth()->format('Y-m-d') . ' - ' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.jobs')
            ->assertJsonFragment(['id' => $recentJob->id])
            ->assertJsonMissing(['id' => $oldJob->id]);
    }
} 