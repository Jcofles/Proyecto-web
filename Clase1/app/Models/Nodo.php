<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nodo extends Model
{
    protected $table = 'nodos';

    protected $fillable = [
        'nombre',
        'latitud',
        'longitud',
        'tipo_id',
        'piso',
    ];

    /**
     * Relaci\u00f3n: origen -> destino
     */
    public function vecinos(): BelongsToMany
    {
        return $this->belongsToMany(
            Nodo::class,
            'conexiones',
            'nodo_origen_id',
            'nodo_destino_id'
        )->withPivot('distancia')->withTimestamps();
    }

    /**
     * Relaci\u00f3n inversa: destino -> origen
     */
    public function vecinosInversos(): BelongsToMany
    {
        return $this->belongsToMany(
            Nodo::class,
            'conexiones',
            'nodo_destino_id',
            'nodo_origen_id'
        )->withPivot('distancia')->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipo()
    {
        return $this->belongsTo(NodoTipo::class, 'tipo_id');
    }

    // Accessor para mantener compatibilidad
    public function getTipoAttribute()
    {
        return $this->tipo()->value('nombre');
    }

}
