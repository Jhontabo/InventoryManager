<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableProduct extends Model
{
    use HasFactory;

    protected $table = 'products'; // Sigue apuntando a 'products'

    protected $fillable = [
        'name',
        'description',
        'available_quantity',
        'laboratory_id',
        'serial_number',
        'acquisition_date',
        'unit_cost',
        'status',
        'loan_status',
        'product_type',
        'available_for_loan',
        'image',
        'user_id',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
    ];

    // Relaciones

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'product_id');
    }

    public function pendingLoans()
    {
        return $this->loans()->where('status', Loan::STATUS_PENDING);
    }

    // Accesor para ubicación
    public function getLocationAttribute(): string
    {
        return $this->laboratory ? $this->laboratory->location : 'No location assigned';
    }
}
