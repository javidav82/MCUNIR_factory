<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FactoryStatus;

class FactoryStatusFactory extends Factory
{
    protected $model = FactoryStatus::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'status' => $this->faker->randomElement(['operational', 'maintenance', 'offline']),
            'last_maintenance' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'next_maintenance' => $this->faker->dateTimeBetween('now', '+1 month'),
            'total_jobs' => $this->faker->numberBetween(100, 1000),
            'completed_jobs' => $this->faker->numberBetween(0, 1000),
            'pending_jobs' => $this->faker->numberBetween(0, 100),
            'failed_jobs' => $this->faker->numberBetween(0, 50),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function operational()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'operational',
            ];
        });
    }

    public function maintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'maintenance',
            ];
        });
    }

    public function offline()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'offline',
            ];
        });
    }
} 