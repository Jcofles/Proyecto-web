<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NodoTipo extends Model
{
    protected $table = 'nodo_tipos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function nodos()
    {
        return $this->hasMany(Nodo::class, 'tipo_id');
    }
}
