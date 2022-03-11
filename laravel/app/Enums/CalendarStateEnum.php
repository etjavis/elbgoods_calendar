<?php
namespace App\Enums;

/**
 * Enum mit allen möglichen Statuswerten für eine Buchung am Kalender
 * Zudem wird das Enum fürs hinzufügen der Datensätze in der Datenbank verwendet
 */
enum CalendarStateEnum: int
{
    case Requested = 1;
    case Tentative = 2;
    case Booked = 3;
}