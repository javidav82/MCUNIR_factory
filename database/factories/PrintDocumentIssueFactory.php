<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PrintDocumentIssue;
use App\Models\PrintDocument;
use App\Models\User;

class PrintDocumentIssueFactory extends Factory
{
    protected $model = PrintDocumentIssue::class;

    public function definition()
    {
        return [
            'print_document_id' => PrintDocument::factory(),
            'issue' => $this->faker->paragraph,
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => $this->faker->randomElement(['pending', 'resolved', 'rejected']),
            'reported_by' => User::factory(),
            'resolved_by' => null,
            'resolved_at' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
} 