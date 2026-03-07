<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, HasRoles, LogsActivity, Notifiable, Searchable;

    // Nombre de la tabla
    protected $table = 'users';

    // ✅ NO redefinimos primaryKey: Laravel asume 'id' automáticamente

    // Atributos asignables en masa
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'status',
        'custom_fields',
        'avatar_url',
        'document_number',

    ];

    // Atributos ocultos
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts
    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'password' => 'hashed',
        ];
    }

    public $timestamps = true;

    // Método para Filament: control de acceso al panel
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active'; // ✅ Usar 'status', no 'estado'
    }

    // Método para obtener avatar en Filament
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function scopeProfessors($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'docente'); // Ajusta al nombre de tu rol
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['password', 'remember_token']);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'document_number' => $this->document_number,
            'status' => $this->status,
        ];
    }
}
