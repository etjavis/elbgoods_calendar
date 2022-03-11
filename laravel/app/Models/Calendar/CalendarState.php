<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarState extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Verknüpft alle gebuchten Zeiten je Status
     *
     * @return HasMany
     */
    public function calendarBookedTime(): HasMany {
        return $this->hasMany(CalendarBookedTime::class);
    }
}
