<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PrintJob;
use App\Models\User;

class PrintJobFactory extends Factory
{
    protected $model = PrintJob::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'job_status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'document_status' => $this->faker->randomElement(['pending', 'validated', 'rejected']),
            'document_type' => $this->faker->randomElement(['PDF', 'DOC', 'DOCX']),
            'pages' => $this->faker->numberBetween(1, 100),
            'format' => $this->faker->randomElement(['A4', 'Letter', 'Legal']),
            'last_validation' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'feedback' => $this->faker->optional()->paragraph,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'job_status' => 'pending',
            ];
        });
    }

    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'job_status' => 'processing',
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'job_status' => 'completed',
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'job_status' => 'failed',
            ];
        });
    }

    public function withFeedback()
    {
        return $this->state(function (array $attributes) {
            return [
                'feedback' => $this->faker->paragraph,
            ];
        });
    }
} 