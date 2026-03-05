<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory {
    public function definition(): array {
        return [
            'plan' => $this->faker->randomElement(['basic', 'standard', 'premium']),
            'amount' => (string) $this->faker->numberBetween(500, 5000),
            'discount' => (string) $this->faker->numberBetween(0, 500),
            'invoice' => 'INV-' . $this->faker->unique()->numerify('######'),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
			'user_id' => 1,
			'created_by' => 1,
			'created_at' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
			'updated_at' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
        ];
    }
}