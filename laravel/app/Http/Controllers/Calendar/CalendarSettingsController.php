<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Calendar\CalendarAllowedBookingTime;
use App\Models\Calendar\CalendarDefaultTimeRange;
use App\Models\Calendar\CalendarState;
use Illuminate\Http\Request;

class CalendarSettingsController extends Controller
{
    /**
     * Gibt alle möglichen Settings für die Kalender-Logik zurück
     *
     * @return array
     */
    public function index(): array
    {
        return [
            'states' => CalendarState::get(),
            'default_time_ranges' => CalendarDefaultTimeRange::pluck('name'),
            'allowed_booking_times_per_day' => CalendarAllowedBookingTime::get()
        ];
    }
}
