<?php

namespace Database\Factories;

use App\Models\DocumentIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentIssueFactory extends Factory
{
    protected $model = DocumentIssue::class;

    public function definition()
    {
        return [
            'job_id' => 'JOB' . $this->faker->unique()->numberBetween(1000, 9999),
            'title' => $this->faker->sentence,
            'issue_type' => $this->faker->randomElement(['format', 'content', 'quality', 'other']),
            'status' => $this->faker->randomElement(['pending', 'reviewing', 'resolved', 'rejected']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'description' => $this->faker->paragraph,
            'document_type' => $this->faker->randomElement(['PDF', 'DOC', 'DOCX']),
            'pages' => $this->faker->numberBetween(1, 100),
            'format' => $this->faker->randomElement(['A4', 'Letter', 'Legal']),
            'document_path' => $this->faker->filePath(),
            'reported_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
} 