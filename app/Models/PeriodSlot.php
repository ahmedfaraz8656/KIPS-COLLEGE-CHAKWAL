<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodSlot extends Model
{
    protected $fillable = ['period_number', 'start_time', 'end_time'];
    protected $casts = ['start_time' => 'datetime:H:i', 'end_time' => 'datetime:H:i'];
}
