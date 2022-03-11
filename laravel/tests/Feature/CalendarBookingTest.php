<?php

namespace Tests\Feature;

use App\Enums\CalendarStateEnum;
use App\Models\Calendar\CalendarBookedTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarBookingTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsSpecifiedData()
    {
        CalendarBookedTime::factory(10)->create();

        // Zeitraum ist laut definition 30 Tage, da die Factory innerhalb der 30 Tagen alle Zeilen anlegt müssen wir alle Datensätze finden
        $response = $this->get(route('api.calendar-booking.index'));
        $response->assertStatus(200);
        $response->assertJson(CalendarBookedTime::with('calendarState')->get()->toArray());

        // Wenn wir weit genug in der Zukunft schauen, dann dürfen wir keine Datensätze finden
        $response = $this->get(route('api.calendar-booking.index') . '?from=' . now()->addMonths(12)->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function testCanCreate()
    {
        $bookedTimeRaw = CalendarBookedTime::factory()->raw([
            'from_at' => now()->startOfDay()->format('Y-m-d H:i:s'),
            'until_at' => now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s')
        ]);

        $response = $this->post(route('api.calendar-booking.store'), $bookedTimeRaw);
        $response->assertStatus(201);

        $this->assertTrue(CalendarBookedTime::count() === 1);
    }

    public function testValidationErrorOnCreate()
    {
        $bookedTimeRaw = CalendarBookedTime::factory()->raw([
            // Sicher gehen, dass min eine Zeile kaputt ist
            'until_at' => now()->midDay()->addSecond(-2)->format('Y-m-d H:i:s')
        ]);

        $response = $this->post(route('api.calendar-booking.store'), $bookedTimeRaw, ['Accept' => 'application/json']);
        $response->assertStatus(422);

        $this->assertTrue(CalendarBookedTime::count() === 0);
    }

    public function testConflictTentativeOnCreateTentative()
    {
        $fromAt = now()->startOfDay()->format('Y-m-d H:i:s');
        $untilAt = now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s');

        // Daten befüllen die ein Conflict triggern
        CalendarBookedTime::factory(4)->create([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Requested->value
        ]);

        $bookedTimeRaw = CalendarBookedTime::factory()->raw([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Tentative->value
        ]);

        $response = $this->post(route('api.calendar-booking.store'), $bookedTimeRaw, ['Accept' => 'application/json']);
        $response->assertStatus(409);
        $this->assertTrue(CalendarBookedTime::count() === 4);
    }
    
    public function testConflictBookedOnCreateBooked()
    {
        $fromAt = now()->startOfDay()->format('Y-m-d H:i:s');
        $untilAt = now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s');

        // Daten befüllen die ein Conflict triggern
        CalendarBookedTime::factory()->create([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Requested->value
        ]);

        $bookedTimeRaw = CalendarBookedTime::factory()->raw([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Booked->value
        ]);

        $response = $this->post(route('api.calendar-booking.store'), $bookedTimeRaw, ['Accept' => 'application/json']);
        $response->assertStatus(409);
        $this->assertTrue(CalendarBookedTime::count() === 1);
    }
    
    public function testIsBookingPossible()
    {
        $fromAt = now()->startOfDay()->format('Y-m-d H:i:s');
        $untilAt = now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s');

        $bookedTimeRaw = CalendarBookedTime::factory()->raw([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Booked->value
        ]);

        $response = $this->post(route('api.calendar-booking.booking-possible'), $bookedTimeRaw, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson(['booking-possible' => true]);
    }

    
    public function testIsBookingNotPossible()
    {
        $fromAt = now()->startOfDay()->format('Y-m-d H:i:s');
        $untilAt = now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s');

        CalendarBookedTime::factory()->create([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Booked->value
        ]);

        $bookedTimeRaw = CalendarBookedTime::factory()->raw([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Requested->value
        ]);

        $response = $this->post(route('api.calendar-booking.booking-possible'), $bookedTimeRaw, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $response->assertJson(['booking-possible' => false]);
    }

    public function testCanUpdate()
    {
        $fromAt = now()->startOfDay()->format('Y-m-d H:i:s');
        $untilAt = now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s');

        $bookedTime = CalendarBookedTime::factory()->create([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Requested->value
        ]);

        $response = $this->patch(route('api.calendar-booking.update', ['bookedTime' => $bookedTime->getKey()]), ['calendar_state_id' => CalendarStateEnum::Tentative->value]);
        $response->assertStatus(200);
        $this->assertTrue($bookedTime->refresh()->calendar_state_id === CalendarStateEnum::Tentative->value);
        $response->assertJson($bookedTime->toArray());
    }

    public function testCanNotUpdateBooked()
    {
        $fromAt = now()->startOfDay()->format('Y-m-d H:i:s');
        $untilAt = now()->midDay()->addSecond(-1)->format('Y-m-d H:i:s');

        $bookedTime = CalendarBookedTime::factory()->create([
            'from_at' => $fromAt,
            'until_at' => $untilAt,
            'calendar_state_id' => CalendarStateEnum::Booked->value
        ]);

        $response = $this->patch(route('api.calendar-booking.update', ['bookedTime' => $bookedTime->getKey()]), ['calendar_state_id' => CalendarStateEnum::Tentative->value]);
        $response->assertStatus(409);
        $this->assertTrue($bookedTime->refresh()->calendar_state_id === CalendarStateEnum::Booked->value);
    }
    
    public function testCanDeleteBookedTime()
    {
        $bookedTime = CalendarBookedTime::factory()->create();

        $response = $this->delete(route('api.calendar-booking.delete', ['bookedTime' => $bookedTime->getKey()]));
        $response->assertStatus(204);

        $this->assertEmpty($bookedTime->fresh);
    }
}
