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
            'document_name' => $this->faker->word . '.pdf',
            'printer_id' => $this->faker->numberBetween(1, 10),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'copies' => $this->faker->numberBetween(1, 10),
            'color' => $this->faker->boolean,
            'double_sided' => $this->faker->boolean,
            'file_path' => $this->faker->filePath()
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
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