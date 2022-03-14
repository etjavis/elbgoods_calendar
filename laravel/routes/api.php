<?php

use App\Http\Controllers\Calendar\CalendarBookingController;
use App\Http\Controllers\Calendar\CalendarSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/', function () {
    $routes = Route::getRoutes();
    foreach ($routes->getRoutes() as $route) {
        if (mb_strpos($route->getName(), 'api.') !== false) {
            echo $route->getName(). '<br>';
            echo $route->getActionName(). '<br>';
            echo config('app.url') . '/' . $route->uri() . '<br><br><br>';
        }
   }
});

Route::name('api.')->group(function() {
    Route::controller(CalendarSettingsController::class)->group(function() {
        Route::get('/calendar-settings', 'index')->name('calendar-settings.index');
    });

    Route::controller(CalendarBookingController::class)->prefix('calendar-booking')->group(function() {
        Route::get('', 'index')->name('calendar-booking.index');
        Route::post('', 'store')->name('calendar-booking.store');
        Route::post('/booking-possible', 'bookingPossible')->name('calendar-booking.booking-possible');
        Route::patch('/{bookedTime}', 'update')->name('calendar-booking.update');
        Route::delete('/{bookedTime}', 'delete')->name('calendar-booking.delete');
    });
});

