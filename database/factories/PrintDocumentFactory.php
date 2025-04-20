<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PrintDocument;
use App\Models\PrintJob;

class PrintDocumentFactory extends Factory
{
    protected $model = PrintDocument::class;

    public function definition()
    {
        return [
            'print_job_id' => PrintJob::factory(),
            'file_name' => $this->faker->word . '.pdf',
            'file_path' => 'documents/' . $this->faker->uuid . '.pdf',
            'status' => $this->faker->randomElement(['pending', 'validated', 'invalid']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
} 