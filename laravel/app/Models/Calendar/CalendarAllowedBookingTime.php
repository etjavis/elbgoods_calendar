<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarAllowedBookingTime extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Generiert den String um zu überprüfen, ob die Datumsangaben auf bestimmte Zeiten endet
     *
     * @return array<string>
     */
    public static function generatForEndsWithCheck(): array
    {
        // Es wird ein 11:59:59,23:59:59 generiert um nur bestimmte Zeiten zu erlauben
        $regexPartFrom = implode(',', CalendarAllowedBookingTime::pluck('from')->toArray());
        $regexPartUntil = implode(',', CalendarAllowedBookingTime::pluck('until')->toArray());

        return [$regexPartFrom, $regexPartUntil];
    }
}