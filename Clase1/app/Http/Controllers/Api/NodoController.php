<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nodo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class NodoController extends Controller
{
    /**
     * Obtener todos los nodos (respuesta JSON simple).
     */
    public function index(): JsonResponse
    {
        return response()->json(Nodo::all());
    }

    /**
     * Guardar un nuevo nodo (usado por la app Flutter).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'tipo' => ['required', Rule::in(['salon', 'pasillo', 'ba\u00f1o', 'escaleras'])],
            'piso' => 'required|integer',
        ]);

        $nodo = Nodo::create($data);

        return response()->json($nodo, 201);
    }

    /**
     * Conectar dos nodos (crea una fila en la tabla `conexiones`).
     * Parámetros: nodo_origen_id, nodo_destino_id, distancia, (opcional) bidireccional
     */
    public function conectar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nodo_origen_id' => 'required|integer|exists:nodos,id',
            'nodo_destino_id' => 'required|integer|exists:nodos,id|different:nodo_origen_id',
            'distancia' => 'required|numeric|min:0',
            'bidireccional' => 'sometimes|boolean',
        ]);

        $origen = Nodo::findOrFail($data['nodo_origen_id']);
        $destinoId = $data['nodo_destino_id'];
        $distancia = $data['distancia'];

        // Crear la conexi\u00f3n en la tabla pivote (evita duplicados)
        $origen->vecinos()->syncWithoutDetaching([
            $destinoId => ['distancia' => $distancia]
        ]);

        // Si se pide, crea tambi\u00e9n la conexi\u00f3n inversa (opcional)
        if (!empty($data['bidireccional'])) {
            $destino = Nodo::findOrFail($destinoId);
            $destino->vecinos()->syncWithoutDetaching([
                $origen->id => ['distancia' => $distancia]
            ]);
        }

        return response()->json(['message' => 'Conexi\u00f3n creada'], 201);
    }
}
