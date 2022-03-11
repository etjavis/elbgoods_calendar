<?php

namespace App\Models\Calendar;

use App\Enums\CalendarStateEnum;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarBookedTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_at',
        'until_at',
        'calendar_state_id',
    ];

    protected $dates= [
        'from_at',
        'until_at'
    ];

    /**
     * Verknüpft den Status an die gebuchte Zeit
     *
     * @return BelongsTo
     */
    public function calendarState(): BelongsTo
    {
        return $this->belongsTo(CalendarState::class);
    }

    /**
     * Gibt den Standard-Query zurück um alle gebuchten Einträge in einem Range zu selektieren
     * 
     * @param Carbon $from Startdatum
     * @param Carbon $until Bisdatum
     * @param bool $takeFullDay
     */
    public static function getBookedTimesInRangeQuery(Carbon $from, Carbon $until, bool $takeFullDay = false): Builder
    {
        if ($takeFullDay) {
            $from = $from->startOfDay();
            $until = $until->endOfDay();
        }

        // Wir bauen eine Klammerung ein, falls später weitere Where Bedingungen hinzugefügt werden
        return CalendarBookedTime::where(function($query) use($from, $until) {
                $query->whereBetween('from_at', [$from, $until])
                    ->orWhereBetween(DB::raw('"' . $from . '"'), [DB::raw('from_at'), DB::raw('until_at')])
                    ->orWhereBetween(DB::raw('"' . $until . '"'), [DB::raw('from_at'), DB::raw('until_at')]);
            });
    }

    /**
     * Überprüft ob die Buchung erlaubt ist
     *
     * Bei "Booked" dürfen andere Status werte nicht im gleichen Zeitraum liegen
     * Bei "Tentative" dürfen andere Einträge für diesen Zeitraum nur maximal 3x vorkommen 
     * 
     * @return boolean
     */
    public function bookingAllowed(): bool
    {
        $amountOfConflicts = 0;
        $currentId = $this->getKey();

        // Es darf in der Range keine Buchung existieren die schon auf Booked steht
        $amountOfConflicts += self::getBookedTimesInRangeQuery($this->from_at, $this->until_at)
            ->where('calendar_state_id', CalendarStateEnum::Booked->value)
            ->when($currentId !== null, function ($query) use($currentId) {
                // Wenn ich schon existiere, dann möchte ich mich selbst nicht in der Überprüfung haben
                $query->where('id', '!=', $currentId);
            })
            ->count();

        // Wenn ich auf booked stehe, dann darf in der DB für die Range kein Eintrag existieren
        if ($this->calendar_state_id === CalendarStateEnum::Booked->value) {
            $amountOfConflicts += self::getBookedTimesInRangeQuery($this->from_at, $this->until_at)
            ->when($currentId !== null, function ($query) use($currentId) {
                $query->where('id', '!=', $currentId);
            })
            ->count();
        }

        // Überprüfen, ob wir schon Tentative Einträge haben
        $hasTentative = self::getBookedTimesInRangeQuery($this->from_at, $this->until_at)
            ->where('calendar_state_id', CalendarStateEnum::Tentative->value)
            ->when($currentId !== null, function ($query) use($currentId) {
                $query->where('id', '!=', $currentId);
            })->count() > 0;

        // Überprüfen, ob wir schon ein Tentative Eintrag habe oder ob ich selbst ein Tentative Eintrag bin
        if ($hasTentative || $this->calendar_state_id === CalendarStateEnum::Tentative->value) {
            // Es darf in der Range maximal 3 weitere Buchungen existieren
            $amountOfConflicts += self::getBookedTimesInRangeQuery($this->from_at, $this->until_at)
                ->where('calendar_state_id', '!=', CalendarStateEnum::Booked->value)
                ->when($currentId !== null, function ($query) use($currentId) {
                    $query->where('id', '!=', $currentId);
                })
                ->count() > 3 ? 1 : 0;
        }

        return $amountOfConflicts === 0;
    }
}