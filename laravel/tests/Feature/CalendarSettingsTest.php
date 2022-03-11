<?php

namespace Tests\Feature;

use App\Models\Calendar\CalendarAllowedBookingTime;
use App\Models\Calendar\CalendarDefaultTimeRange;
use App\Models\Calendar\CalendarState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testIndexReturnsSpecifiedData()
    {
        $response = $this->get(route('api.calendar-settings.index'));

        $response->assertStatus(200);
        
        $response->assertJson([
            'states' => CalendarState::get()->toArray(),
            'default_time_ranges' => CalendarDefaultTimeRange::pluck('name')->toArray(),
            'allowed_booking_times_per_day' => CalendarAllowedBookingTime::get()->toArray()
        ]);
    }
}
