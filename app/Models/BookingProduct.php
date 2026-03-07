<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookingProduct extends Pivot
{
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    // Puedes añadir métodos adicionales aquí
}
