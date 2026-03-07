<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleEquipment extends Pivot
{
    protected $table = 'schedule_equipment';

    protected $fillable = [
        'schedule_id',
        'equipment_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];
}
