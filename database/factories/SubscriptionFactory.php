<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Netflix', 'Spotify', 'Amazon Prime', 'Gym Member', 'Cloud Storage', 'Mobile Plan']),
            'category' => $this->faker->randomElement(['Entertainment', 'Utility', 'Fitness', 'Software']),
            'price' => $this->faker->randomElement([980, 1480, 2980, 5000, 9800, 12000]),
            'billing_cycle' => $this->faker->randomElement(['monthly', 'yearly']),
            'next_billing_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'memo' => $this->faker->optional()->sentence(),
        ];
    }
}