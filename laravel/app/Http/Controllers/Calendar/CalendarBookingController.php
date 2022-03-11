<?php

namespace App\Http\Controllers\Calendar;

use App\Enums\CalendarStateEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingTimeRequest;
use App\Models\Calendar\CalendarAllowedBookingTime;
use App\Models\Calendar\CalendarBookedTime;
use App\Models\Calendar\CalendarDefaultTimeRange;
use Illuminate\Http\Request;

class CalendarBookingController extends Controller
{
    /**
     * Selektiert alle gebuchten Zeiten
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'until' => ['sometimes', 'date_format:Y-m-d', 'after:from'],
            'range' => ['sometimes', 'exists:calendar_default_time_ranges,name'],
        ]);

        // From kann immer Dynamisch gesetzt werden, ansonsten einfach heute
        $from = $request->date('from') ?? now();

        if (($range = $request->get('range', null)) !== null) {
            // Erhöht den heutigen Tag mit der, in der Datenbankhinterlegte, Carbon Funktion
            $until = now()->{CalendarDefaultTimeRange::where('name', $range)->first()->carbon_function}();
        } else {
            // Standardmäßig 30 Tage
            $until = $request->date('until') ?? now()->addDays(30);
        }

        // Selektiert alle Buchungen die in dem Zeitraum vorhanden sind
        return CalendarBookedTime::getBookedTimesInRangeQuery($from, $until, true)->with('calendarState')->get();
    }

    /**
     * Bucht eine Zeit mit einem Status
     *
     * @param Request $request
     */
    public function store(StoreBookingTimeRequest $request)
    {
        $bookedTime = new CalendarBookedTime($request->validated());

        if ($bookedTime->bookingAllowed()) {
            $bookedTime->save();
            return response()->json($bookedTime, 201);
        } else {
            abort(409, 'Die Buchung konnte nicht ausgeführt werden, der Zeitraum ist schon vergeben.');
        }
    }

    /**
     * Gibt zurück, ob eine Buchung möglich ist
     *
     */
    public function bookingPossible(StoreBookingTimeRequest $request)
    {
        $bookedTime = new CalendarBookedTime($request->validated());

        return response()->json(['booking-possible' => $bookedTime->bookingAllowed()]);
    }

    /**
     * Bucht eine Zeit mit einem Status
     *
     * @param Request $request
     */
    public function update(Request $request, CalendarBookedTime $bookedTime)
    {
        list($endsWithFrom, $endsWithUntil) = CalendarAllowedBookingTime::generatForEndsWithCheck();

        $data = $request->validate([
            'from_at' => ['date_format:Y-m-d H:i:s', 'required_with:until_at', 'ends_with:' . $endsWithFrom],
            'until_at' => ['date_format:Y-m-d H:i:s', 'after:from_at' , 'required_with:from_at', 'ends_with:' . $endsWithUntil],
            'calendar_state_id' => ['sometimes', 'integer', 'exists:calendar_states,id'],
        ]);

        // Wenn der Status schon auf "Booked" steht, dann ist ein Update nicht erlaubt
        if ($bookedTime->calendar_state_id === CalendarStateEnum::Booked->value) {
            abort(409, 'Die Buchung konnte nicht ausgeführt werden, der Status erlaubt dies nicht.');
        }

        $bookedTime->fill($data);

        if ($bookedTime->bookingAllowed()) {
            $bookedTime->save();
            return response()->json($bookedTime, 200);
        } else {
            abort(409, 'Die Buchung konnte nicht ausgeführt werden, der Zeitraum ist schon vergeben.');
        }
    }

    /**
     * Löscht ein Eintrag
     *
     * @param CalendarBookedTime $bookedTime
     */
    public function delete(CalendarBookedTime $bookedTime)
    {
        $bookedTime->delete();

        return response()->noContent();
    }
}
