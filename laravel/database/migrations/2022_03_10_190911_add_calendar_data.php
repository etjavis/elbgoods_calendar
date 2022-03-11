<?php

use App\Enums\CalendarStateEnum;
use App\Models\Calendar\CalendarAllowedBookingTime;
use App\Models\Calendar\CalendarDefaultTimeRange;
use App\Models\Calendar\CalendarState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addStates();
        $this->addAllowedBookingTimePerDay();
        $this->addDefaultTimeRanges();

        Schema::create('calendar_booked_times', function (Blueprint $table) {
            $table->id();

            $table->timestamp('from_at');
            $table->timestamp('until_at');
            $table->foreignId('calendar_state_id')->constrainted()->restrictOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Erstellt eine Tabelle, in den alle möglichen Status hinterlegt sind
     *
     * @return void
     */
    private function addStates()
    {
        Schema::create('calendar_states', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->timestamps();
        });

        foreach (CalendarStateEnum::cases() as $entity) {
            $calendarState = new CalendarState();
            $calendarState->id = $entity->value;
            $calendarState->name = $entity->name;
            $calendarState->save();
        }
    }

    /**
     * Erstellt eine Tabelle, welche alle erlaubten Tagesbereiche beinhaltet
     *
     * @return void
     */
    private function addAllowedBookingTimePerDay()
    {
        Schema::create('calendar_allowed_booking_times', function (Blueprint $table) {
            $table->id();

            $table->time('from');
            $table->time('until');

            $table->timestamps();
        });

        $calendarState = new CalendarAllowedBookingTime();
        $calendarState->from = '00:00:00';
        $calendarState->until = '11:59:59';
        $calendarState->save();
        
        $calendarState = new CalendarAllowedBookingTime();
        $calendarState->from = '12:00:00';
        $calendarState->until = '23:59:59';
        $calendarState->save();
    }

    /**
     * Speichert die Standardzeiträume und die dazugehörige Carbon funktion
     *
     * @return void
     */
    private function addDefaultTimeRanges()
    {
        Schema::create('calendar_default_time_ranges', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('carbon_function');

            $table->timestamps();
        });

        $calendarState = new CalendarDefaultTimeRange();
        $calendarState->name = 'week';
        $calendarState->carbon_function = 'addWeek';
        $calendarState->save();
        
        $calendarState = new CalendarDefaultTimeRange();
        $calendarState->name = 'month';
        $calendarState->carbon_function = 'addMonth';
        $calendarState->save();
        
        $calendarState = new CalendarDefaultTimeRange();
        $calendarState->name = 'quartar';
        $calendarState->carbon_function = 'addQuartar';
        $calendarState->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_states');
        Schema::dropIfExists('calendar_booked_times');
        Schema::dropIfExists('calendar_allowed_booking_times');
        Schema::dropIfExists('calendar_default_time_ranges');
    }
};
