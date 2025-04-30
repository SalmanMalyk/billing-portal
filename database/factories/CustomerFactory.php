<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->optional(0.9)->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->optional(0.8)->address(),
            'package_id' => Package::factory(),
            'bill' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->boolean(80), // 80% chance of being active
            'billing_start_date' => fake()->dateTimeBetween('-2 month', 'now'),
        ];
    }

    /**
     * Indicate that the customer is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Indicate that the customer belongs to a specific package.
     */
    public function forPackage(Package $package): static
    {
        return $this->state(fn (array $attributes) => [
            'package_id' => $package->id,
            'bill' => $package->fee, // Set bill based on package fee
        ]);
    }
}
