<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(rand(1, 3), true),
            'description' => fake()->optional(0.7)->paragraph(),
            'fee' => fake()->randomFloat(2, 500, 5000),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Package $package) {
            // Optional configuration after making a package
        })->afterCreating(function (Package $package) {
            // Optional configuration after creating a package
        });
    }

    /**
     * Indicate that the package is a premium package.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Premium ' . fake()->word(),
            'fee' => fake()->randomFloat(2, 5000, 10000),
        ]);
    }

    /**
     * Indicate that the package is a basic package.
     */
    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Basic ' . fake()->word(),
            'fee' => fake()->randomFloat(2, 100, 499),
        ]);
    }
}
