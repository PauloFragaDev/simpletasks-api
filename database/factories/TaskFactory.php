<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'done']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => 'in_progress']);
    }

    public function done(): static
    {
        return $this->state([
            'status'       => 'done',
            'completed_at' => now(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(['priority' => 'high']);
    }

    public function withPastDueDate(): static
    {
        return $this->state([
            'due_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
    }
}
