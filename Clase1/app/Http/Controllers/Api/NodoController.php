<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNodoRequest;
use App\Http\Requests\ConectarNodoRequest;
use App\Models\Nodo;
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
    public function store(StoreNodoRequest $request): JsonResponse
    {
        $data = $request->validated();

        $nodo = Nodo::create($data);

        return response()->json($nodo, 201);
    }

    /**
     * Conectar dos nodos (crea una fila en la tabla `conexiones`).
     * Parámetros: nodo_origen_id, nodo_destino_id, distancia, (opcional) bidireccional
     */
    public function conectar(ConectarNodoRequest $request): JsonResponse
    {
        $data = $request->validated();

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
