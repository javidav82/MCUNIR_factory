<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PrintJob;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueryInfoDataTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_access_query_info_data_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/query/info-data');

        $response->assertStatus(200)
            ->assertViewIs('query.info-data')
            ->assertSee('Query Info Data');
    }

    /** @test */
    public function it_can_load_print_jobs_list()
    {
        $jobs = PrintJob::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs');

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
    public function it_can_filter_print_jobs_by_status()
    {
        $pendingJob = PrintJob::factory()->create(['job_status' => 'pending']);
        $completedJob = PrintJob::factory()->create(['job_status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.jobs')
            ->assertJsonFragment(['id' => $pendingJob->id])
            ->assertJsonMissing(['id' => $completedJob->id]);
    }

    /** @test */
    public function it_can_search_print_jobs()
    {
        $job1 = PrintJob::factory()->create(['title' => 'Test Job 1']);
        $job2 = PrintJob::factory()->create(['title' => 'Another Job']);

        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs?search=Test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.jobs')
            ->assertJsonFragment(['id' => $job1->id])
            ->assertJsonMissing(['id' => $job2->id]);
    }

    /** @test */
    public function it_can_filter_print_jobs_by_date_range()
    {
        $oldJob = PrintJob::factory()->create(['created_at' => now()->subMonths(2)]);
        $recentJob = PrintJob::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs?date_range=' . now()->subMonth()->format('Y-m-d') . ' - ' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.jobs')
            ->assertJsonFragment(['id' => $recentJob->id])
            ->assertJsonMissing(['id' => $oldJob->id]);
    }

    /** @test */
    public function it_can_view_print_job_details()
    {
        $job = PrintJob::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/api/user/print-jobs/{$job->id}");

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
    public function it_returns_404_for_nonexistent_job()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_paginate_print_jobs()
    {
        PrintJob::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs?page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'jobs',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'pagination' => [
                        'current_page' => 2
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get('/query/info-data');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_handles_empty_search_results()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs?search=nonexistent');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.jobs');
    }

    /** @test */
    public function it_validates_date_range_format()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/user/print-jobs?date_range=invalid-format');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_range']);
    }
} 