<?php

namespace Database\Factories;

use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Request::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clientName' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'problemText' => fake()->paragraph(),
            'status' => fake()->randomElement(['new', 'assigned', 'in_progress', 'done', 'canceled']),
            'assignedTo' => null,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the request is new.
     */
    public function asNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
            'assignedTo' => null,
        ]);
    }

    /**
     * Indicate that the request is assigned to a master.
     */
    public function assignedTo(User $master): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'assignedTo' => $master->id,
        ]);
    }

    /**
     * Indicate that the request is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the request is done.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
        ]);
    }

    /**
     * Indicate that the request is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
        ]);
    }
}
