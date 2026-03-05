<?php

namespace Database\Factories;

use App\Models\EventLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventLogFactory extends Factory
{
    protected $model = EventLog::class;

    public function definition(): array
    {
        return [
            'action_type' => $this->faker->randomElement([
                'login',
                'logout',
                'create',
                'update',
                'delete'
            ]),
            'action_by' => $this->faker->name(),
            'action' => $this->faker->sentence(4),

            'user_id' => User::inRandomOrder()->first()->id,
            'created_by' => User::inRandomOrder()->first()->id,

            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => now(),
        ];
    }
}
