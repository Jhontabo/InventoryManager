<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipment extends Model
{
    protected $table = 'equipments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'model',
        'serial_number',
        'laboratory_id',
        'status',
        // otros campos necesarios
    ];

    /**
     * Relación con el laboratorio al que pertenece el equipo
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id');
    }

    /**
     * Relación muchos-a-muchos con horarios (schedules)
     */
    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_equipment')
            ->using(ScheduleEquipment::class) // Usando el modelo pivot personalizado
            ->withPivot('quantity') // Incluye el campo adicional
            ->withTimestamps(); // Incluye los timestamps de la tabla pivot
    }

    /**
     * Accesor para mostrar nombre completo del equipo
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->model}) - #{$this->serial_number}";
    }
}
