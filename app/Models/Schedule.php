<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schedules';

    protected $primaryKey = 'id';

    protected $attributes = [
        'color' => '#4b82f6',
    ];

    protected $fillable = [
        'title',
        'start_at',
        'end_at',
        'color',
        'description',
        'is_available',
        'reservation_status',
        'laboratory_id',
        'user_id',
        'type',
        'recurrence_days',
        'recurrence_until',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function unstructured()
    {
        return $this->hasOne(ScheduleUnstructured::class);
    }

    public function structured()
    {
        return $this->hasOne(ScheduleStructured::class, 'schedule_id'); // Especifica la clave foránea
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_schedule')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function equipments(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'schedule_equipment')
            ->using(ScheduleEquipment::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function booking()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    public function getTimeRangeAttribute(): string
    {
        return $this->start_at.' - '.$this->end_at;
    }
}
