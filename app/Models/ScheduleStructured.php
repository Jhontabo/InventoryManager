<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleStructured extends Model
{
    use HasFactory;

    protected $table = 'schedule_structured';

    protected $fillable = [
        'schedule_id',
        'academic_program_name', // reemplazo
        'semester',
        'student_count',
        'group_count',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id'); // Especifica la clave foránea
    }
}
