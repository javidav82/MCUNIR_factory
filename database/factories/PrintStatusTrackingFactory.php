<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PrintStatusTracking;
use App\Models\PrintJob;
use App\Models\PrintDocument;
use App\Models\User;

class PrintStatusTrackingFactory extends Factory
{
    protected $model = PrintStatusTracking::class;

    public function definition()
    {
        $trackableType = $this->faker->randomElement(['print_job', 'print_document']);
        $trackableId = $trackableType === 'print_job' 
            ? PrintJob::factory() 
            : PrintDocument::factory();

        return [
            'print_job_id' => $trackableType === 'print_job' ? $trackableId : null,
            'print_document_id' => $trackableType === 'print_document' ? $trackableId : null,
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'validated', 'invalid']),
            'changed_by' => User::factory(),
            'comments' => $this->faker->sentence,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
} 