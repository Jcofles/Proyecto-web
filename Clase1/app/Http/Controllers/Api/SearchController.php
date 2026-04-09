<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterNodoRequest;
use App\Http\Requests\FilterUserRequest;
use App\Models\Nodo;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * EJEMPLO: Controlador de Búsqueda y Filtros en Tiempo Real
 * 
 * Este controlador demuestra cómo implementar búsqueda/filtros usando los Form Requests.
 * 
 * Para usar estos endpoints, descomenta el archivo y agrega las rutas:
 * 
 * routes/api.php:
 * Route::middleware('auth:sanctum')->group(function () {
 *     Route::get('/search/nodos', [SearchController::class, 'searchNodos']);
 *     Route::get('/search/usuarios', [SearchController::class, 'searchUsuarios']);
 * });
 */

class SearchController extends Controller
{
    /**
     * Buscar nodos en tiempo real con filtros
     * 
     * GET /api/search/nodos?q=Salón&tipo_id=1&piso=1&per_page=10
     *
     * Parámetros:
     * - q: búsqueda por nombre (opcional)
     * - tipo_id: filtrar por tipo de nodo (opcional)
     * - piso: filtrar por piso (opcional)
     * - per_page: resultados por página (default: 15, máximo: 100)
     */
    public function searchNodos(FilterNodoRequest $request): JsonResponse
    {
        $filters = $request->validated();
        
        $query = Nodo::query();
        
        // Búsqueda por nombre
        if (!empty($filters['q'])) {
            $query->where('nombre', 'like', '%' . $filters['q'] . '%');
        }
        
        // Filtrar por tipo
        if (!empty($filters['tipo_id'])) {
            $query->where('tipo_id', $filters['tipo_id']);
        }
        
        // Filtrar por piso
        if (!empty($filters['piso'])) {
            $query->where('piso', $filters['piso']);
        }
        
        $perPage = $filters['per_page'] ?? 15;
        
        $results = $query->paginate($perPage);
        
        return response()->json($results);
    }

    /**
     * Buscar usuarios en tiempo real con filtros
     * 
     * GET /api/search/usuarios?q=Juan&status=activo&per_page=10
     *
     * Parámetros:
     * - q: búsqueda por nombre o email (opcional)
     * - status: filtrar por estado: activo|inactivo|bloqueado|eliminado (opcional)
     * - per_page: resultados por página (default: 15, máximo: 100)
     */
    public function searchUsuarios(FilterUserRequest $request): JsonResponse
    {
        $filters = $request->validated();
        
        $query = User::query();
        
        // Búsqueda por nombre o email
        if (!empty($filters['q'])) {
            $searchTerm = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombres', 'like', $searchTerm)
                  ->orWhere('apellidos', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }
        
        // Filtrar por estado
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $perPage = $filters['per_page'] ?? 15;
        
        $results = $query->paginate($perPage);
        
        return response()->json($results);
    }
}
