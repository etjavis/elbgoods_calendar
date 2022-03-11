<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarDefaultTimeRange extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];
}