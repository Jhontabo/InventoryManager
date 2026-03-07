<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleUnstructured extends Model
{
    use HasFactory;

    protected $table = 'schedule_unstructured';

    protected $fillable = [
        'schedule_id',
        'project_type',
        'academic_program',
        'semester',
        'applicants',
        'research_name',
        'advisor',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
