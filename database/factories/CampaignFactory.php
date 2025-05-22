<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Models\Campaign;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 week');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 months');

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'goal_amount' => $this->faker->randomFloat(2, 1000, 100000),
            'current_amount' => $this->faker->randomFloat(2, 0, 999),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->faker->randomElement([
                Campaign::STATUS_PENDING,
                Campaign::STATUS_APPROVED,
                Campaign::STATUS_ACTIVE,
                Campaign::STATUS_COMPLETED,
                Campaign::STATUS_FAILED,
                Campaign::STATUS_REJECTED,
                Campaign::STATUS_CANCELLED,
            ]),
        ];
    }
}
