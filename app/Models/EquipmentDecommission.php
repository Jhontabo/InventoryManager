<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDecommission extends Model
{
    protected $fillable = [
        'product_id',
        'reason',          // Campo de texto libre para el motivo
        'responsible_user_id',
        'student_document',
        'academic_program', // Podría ser ENUM o string según tu implementación
        'semester',
        'decommission_date',
        'expected_return_date',
        'registered_by',
        'reversed_by',
        'reversed_at',
        'observations',
        'damage_type',
    ];

    protected $casts = [
        'decommission_date' => 'date',
        'expected_return_date' => 'date',
        'reversed_at' => 'datetime',
        'damage_type' => 'string',
    ];

    /**
     * Relación con el producto dado de baja
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Estudiante responsable (solo cuando reason = 'damaged')
     */
    public function responsibleStudent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id')
            ->whereHas('roles', fn ($q) => $q->where('name', 'estudiante'));
    }

    // En app/Models/EquipmentDecommission.php
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Usuario que registró la baja
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Usuario que revirtió la baja (si aplica)
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Scope para bajas por daño
     */
    public function scopeDamaged($query)
    {
        return $query->where('decommission_type', 'damaged');
    }

    /**
     * Scope para equipos en mantenimiento
     */
    public function scopeMaintenance($query)
    {
        return $query->where('decommission_type', 'maintenance');
    }

    /**
     * Scope para bajas reversibles
     */
    public function scopeReversible($query)
    {
        return $query->whereIn('decommission_type', ['maintenance', 'damaged']);
    }
}
