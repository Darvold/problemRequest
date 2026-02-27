<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fio' => fake()->name(),
            'login' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(['dispatcher', 'master']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the user is a dispatcher.
     */
    public function dispatcher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'dispatcher',
        ]);
    }

    /**
     * Indicate that the user is a master.
     */
    public function master(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'master',
        ]);
    }
}
