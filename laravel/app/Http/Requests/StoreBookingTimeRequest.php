<?php

namespace App\Http\Requests;

use App\Models\Calendar\CalendarAllowedBookingTime;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingTimeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        list($endsWithFrom, $endsWithUntil) = CalendarAllowedBookingTime::generatForEndsWithCheck();

        return [
            'from_at' => ['required', 'date_format:Y-m-d H:i:s', 'ends_with:' . $endsWithFrom],
            'until_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:from_at' , 'ends_with:' . $endsWithUntil],
            'calendar_state_id' => ['required', 'integer', 'exists:calendar_states,id'],
        ];
    }
}
