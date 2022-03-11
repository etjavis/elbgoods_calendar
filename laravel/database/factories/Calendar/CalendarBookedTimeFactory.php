<?php

namespace Database\Factories\Calendar;

use App\Models\Calendar\CalendarState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calendar\CalendarBookedTime>
 */
class CalendarBookedTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'from_at' => $this->faker->dateTimeBetween(now(), now()->addWeek())->format('Y-m-d H:i:s'),
            'until_at' => $this->faker->dateTimeBetween(now()->addWeek(), now()->addMonth())->format('Y-m-d H:i:s'),
            'calendar_state_id' => CalendarState::inRandomOrder()->first()->getKey()
        ];
    }
}
