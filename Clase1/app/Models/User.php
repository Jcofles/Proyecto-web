<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'password',
        'email_verified_at',
        'email_verification_token',
        'email_verification_expires_at',
        'status_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function nodos()
    {
        return $this->hasMany(Nodo::class);
    }

    public function statusRelation()
    {
        return $this->belongsTo(UserStatus::class, 'status_id');
    }

    // Accessor para mantener compatibilidad con código existente
    public function getStatusAttribute()
    {
        return $this->statusRelation()->value('nombre') ?? 'activo';
    }

    // Mutator para permitir asignar por nombre
    public function setStatusAttribute($value)
    {
        if (is_string($value)) {
            $status = UserStatus::where('nombre', $value)->first();
            if ($status) {
                $this->attributes['status_id'] = $status->id;
            }
        } elseif (is_numeric($value)) {
            $this->attributes['status_id'] = $value;
        }
    }

    // Accessor para mantener compatibilidad con $user->name
    public function getNameAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
}
