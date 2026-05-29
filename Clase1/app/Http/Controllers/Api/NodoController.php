<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNodoRequest;
use App\Http\Requests\ConectarNodoRequest;
use App\Models\Nodo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NodoController extends Controller
{
    /**
     * Obtener todos los nodos desde el array en memoria (sin BD).
     */
    public function index(): JsonResponse
    {
        return response()->json($this->defaultNodos());
    }

    /**
     * Obtener las conexiones entre nodos desde el array en memoria (sin BD).
     */
    public function conexiones(): JsonResponse
    {
        return response()->json($this->defaultConexiones());
    }

    private function defaultNodos(): array
    {
        return [
            ['id' => 1, 'nombre' => 'Entrada Universidad', 'latitud' => 4.15402640, 'longitud' => -74.89564350, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 2, 'nombre' => 'Paso 2', 'latitud' => 4.15409380, 'longitud' => -74.89572580, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 3, 'nombre' => 'Paso 3', 'latitud' => 4.15419060, 'longitud' => -74.89577310, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 4, 'nombre' => 'Paso 4', 'latitud' => 4.15426590, 'longitud' => -74.89585030, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 5, 'nombre' => 'Paso 5', 'latitud' => 4.15431860, 'longitud' => -74.89589410, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 6, 'nombre' => 'Paso 6', 'latitud' => 4.15444080, 'longitud' => -74.89597010, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 7, 'nombre' => 'Paso 7', 'latitud' => 4.15444130, 'longitud' => -74.89602470, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 8, 'nombre' => 'Paso 8', 'latitud' => 4.15458340, 'longitud' => -74.89609780, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 9, 'nombre' => 'Paso 9', 'latitud' => 4.15460250, 'longitud' => -74.89614260, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 10, 'nombre' => 'Paso 10', 'latitud' => 4.15466250, 'longitud' => -74.89611540, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 11, 'nombre' => 'Paso 11', 'latitud' => 4.15467930, 'longitud' => -74.89610100, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 12, 'nombre' => 'Paso 12', 'latitud' => 4.15476320, 'longitud' => -74.89621230, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 13, 'nombre' => 'Paso 13', 'latitud' => 4.15478590, 'longitud' => -74.89630010, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 14, 'nombre' => 'Paso 14', 'latitud' => 4.15493860, 'longitud' => -74.89634430, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 15, 'nombre' => 'Paso 15', 'latitud' => 4.15505250, 'longitud' => -74.89642560, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 16, 'nombre' => 'Paso 16', 'latitud' => 4.15517390, 'longitud' => -74.89647910, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 17, 'nombre' => 'Punto Central', 'latitud' => 4.15523300, 'longitud' => -74.89656430, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 18, 'nombre' => 'Paso 18', 'latitud' => 4.15522400, 'longitud' => -74.89656520, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 19, 'nombre' => 'Paso 19', 'latitud' => 4.15528440, 'longitud' => -74.89658860, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 20, 'nombre' => 'Paso 20', 'latitud' => 4.15535900, 'longitud' => -74.89668800, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 21, 'nombre' => 'Paso 21', 'latitud' => 4.15541980, 'longitud' => -74.89676540, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 22, 'nombre' => 'Paso 22', 'latitud' => 4.15547600, 'longitud' => -74.89678570, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 23, 'nombre' => 'Paso 23', 'latitud' => 4.15552670, 'longitud' => -74.89683030, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 24, 'nombre' => 'Paso 24', 'latitud' => 4.15557910, 'longitud' => -74.89689650, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 25, 'nombre' => 'Paso 25', 'latitud' => 4.15565200, 'longitud' => -74.89701470, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 26, 'nombre' => 'Paso 26', 'latitud' => 4.15575840, 'longitud' => -74.89706830, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 27, 'nombre' => 'Paso 27', 'latitud' => 4.15582650, 'longitud' => -74.89703600, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 28, 'nombre' => 'Paso 28', 'latitud' => 4.15584980, 'longitud' => -74.89706070, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 29, 'nombre' => 'Paso 29', 'latitud' => 4.15584140, 'longitud' => -74.89713550, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 30, 'nombre' => 'Paso 30', 'latitud' => 4.15592520, 'longitud' => -74.89718020, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 31, 'nombre' => 'Paso 31', 'latitud' => 4.15600030, 'longitud' => -74.89724950, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 32, 'nombre' => 'Paso 32', 'latitud' => 4.15610030, 'longitud' => -74.89735480, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 33, 'nombre' => 'Paso 33', 'latitud' => 4.15619660, 'longitud' => -74.89736810, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 34, 'nombre' => 'Paso 34', 'latitud' => 4.15623500, 'longitud' => -74.89742760, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 35, 'nombre' => 'Paso 35', 'latitud' => 4.15622820, 'longitud' => -74.89745440, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 36, 'nombre' => 'Paso 36', 'latitud' => 4.15624620, 'longitud' => -74.89747780, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 37, 'nombre' => 'Paso 37', 'latitud' => 4.15635950, 'longitud' => -74.89753610, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 38, 'nombre' => 'Paso 38', 'latitud' => 4.15638030, 'longitud' => -74.89758160, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 39, 'nombre' => 'Paso 39', 'latitud' => 4.15639010, 'longitud' => -74.89761100, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 40, 'nombre' => 'Paso 40', 'latitud' => 4.15643510, 'longitud' => -74.89760450, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 41, 'nombre' => 'Paso 41', 'latitud' => 4.15645290, 'longitud' => -74.89761870, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 42, 'nombre' => 'Paso 42', 'latitud' => 4.15644660, 'longitud' => -74.89766130, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 43, 'nombre' => 'Paso 43', 'latitud' => 4.15650780, 'longitud' => -74.89772140, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 44, 'nombre' => 'Bloque D', 'latitud' => 4.15653360, 'longitud' => -74.89773380, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 45, 'nombre' => 'Paso 45', 'latitud' => 4.15656930, 'longitud' => -74.89774250, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 46, 'nombre' => 'Paso 46', 'latitud' => 4.15666460, 'longitud' => -74.89777130, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 47, 'nombre' => 'Paso 47', 'latitud' => 4.15679740, 'longitud' => -74.89775370, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 48, 'nombre' => 'Paso 48', 'latitud' => 4.15692010, 'longitud' => -74.89771320, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 49, 'nombre' => 'Cafeteria', 'latitud' => 4.15692990, 'longitud' => -74.89763710, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 50, 'nombre' => 'Paso Cafetería', 'latitud' => 4.15690810, 'longitud' => -74.89758290, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 51, 'nombre' => 'Escalera Cafetería', 'latitud' => 4.15691110, 'longitud' => -74.89764240, 'tipo_id' => 4, 'piso' => 1],
            // Bloque D - interior piso 1
            ['id' => 52, 'nombre' => 'Pasillo D', 'latitud' => 4.1566260, 'longitud' => -74.8975792, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 53, 'nombre' => 'Salón D 111', 'latitud' => 4.1566392, 'longitud' => -74.8975613, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 54, 'nombre' => 'Salón D 101', 'latitud' => 4.1566397, 'longitud' => -74.8975852, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 55, 'nombre' => 'Baños D', 'latitud' => 4.1566339, 'longitud' => -74.8975769, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 56, 'nombre' => 'Pasillo D Interior', 'latitud' => 4.1566702, 'longitud' => -74.8975992, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 57, 'nombre' => 'Salón D 102', 'latitud' => 4.1566858, 'longitud' => -74.8975273, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 58, 'nombre' => 'Pasillo D Fondo', 'latitud' => 4.1566566, 'longitud' => -74.8974634, 'tipo_id' => 2, 'piso' => 1],
            ['id' => 59, 'nombre' => 'Escaleras D', 'latitud' => 4.1565193, 'longitud' => -74.8974427, 'tipo_id' => 4, 'piso' => 1],
            // Bloque D - interior piso 2
            ['id' => 60, 'nombre' => 'Salón D 201', 'latitud' => 4.1565152, 'longitud' => -74.8974485, 'tipo_id' => 2, 'piso' => 2],
            ['id' => 61, 'nombre' => 'Salón D 202', 'latitud' => 4.1565651, 'longitud' => -74.8974541, 'tipo_id' => 2, 'piso' => 2],
            ['id' => 62, 'nombre' => 'Salón D 203', 'latitud' => 4.1565743, 'longitud' => -74.8974548, 'tipo_id' => 2, 'piso' => 2],
            ['id' => 63, 'nombre' => 'Salón D 204', 'latitud' => 4.1565778, 'longitud' => -74.8974290, 'tipo_id' => 2, 'piso' => 2],
            ['id' => 64, 'nombre' => 'Salón D 205', 'latitud' => 4.1565408, 'longitud' => -74.8973930, 'tipo_id' => 2, 'piso' => 2],
        ];
    }

    private function defaultConexiones(): array
    {
        return [
            ['id' => 1, 'nodo_origen' => 1, 'nodo_destino' => 2, 'distancia' => 11.81],
            ['id' => 2, 'nodo_origen' => 2, 'nodo_destino' => 3, 'distancia' => 11.97],
            ['id' => 3, 'nodo_origen' => 3, 'nodo_destino' => 4, 'distancia' => 11.98],
            ['id' => 4, 'nodo_origen' => 4, 'nodo_destino' => 5, 'distancia' => 7.61],
            ['id' => 5, 'nodo_origen' => 5, 'nodo_destino' => 6, 'distancia' => 15.99],
            ['id' => 6, 'nodo_origen' => 6, 'nodo_destino' => 7, 'distancia' => 6.06],
            ['id' => 7, 'nodo_origen' => 7, 'nodo_destino' => 8, 'distancia' => 17.76],
            ['id' => 8, 'nodo_origen' => 8, 'nodo_destino' => 9, 'distancia' => 5.4],
            ['id' => 9, 'nodo_origen' => 9, 'nodo_destino' => 10, 'distancia' => 7.32],
            ['id' => 10, 'nodo_origen' => 10, 'nodo_destino' => 11, 'distancia' => 2.46],
            ['id' => 11, 'nodo_origen' => 11, 'nodo_destino' => 12, 'distancia' => 15.47],
            ['id' => 12, 'nodo_origen' => 12, 'nodo_destino' => 13, 'distancia' => 10.06],
            ['id' => 13, 'nodo_origen' => 13, 'nodo_destino' => 14, 'distancia' => 17.67],
            ['id' => 14, 'nodo_origen' => 14, 'nodo_destino' => 15, 'distancia' => 15.55],
            ['id' => 15, 'nodo_origen' => 15, 'nodo_destino' => 16, 'distancia' => 14.75],
            ['id' => 16, 'nodo_origen' => 16, 'nodo_destino' => 17, 'distancia' => 11.51],
            ['id' => 17, 'nodo_origen' => 17, 'nodo_destino' => 18, 'distancia' => 1.01],
            ['id' => 18, 'nodo_origen' => 18, 'nodo_destino' => 19, 'distancia' => 7.2],
            ['id' => 19, 'nodo_origen' => 19, 'nodo_destino' => 20, 'distancia' => 13.8],
            ['id' => 20, 'nodo_origen' => 20, 'nodo_destino' => 21, 'distancia' => 10.93],
            ['id' => 21, 'nodo_origen' => 21, 'nodo_destino' => 22, 'distancia' => 9.33],
            ['id' => 22, 'nodo_origen' => 22, 'nodo_destino' => 23, 'distancia' => 7.97],
            ['id' => 23, 'nodo_origen' => 23, 'nodo_destino' => 24, 'distancia' => 8.11],
            ['id' => 24, 'nodo_origen' => 24, 'nodo_destino' => 25, 'distancia' => 13.38],
            ['id' => 25, 'nodo_origen' => 25, 'nodo_destino' => 26, 'distancia' => 13.37],
            ['id' => 26, 'nodo_origen' => 26, 'nodo_destino' => 27, 'distancia' => 9.68],
            ['id' => 27, 'nodo_origen' => 27, 'nodo_destino' => 28, 'distancia' => 4.1],
            ['id' => 28, 'nodo_origen' => 28, 'nodo_destino' => 29, 'distancia' => 7.79],
            ['id' => 29, 'nodo_origen' => 29, 'nodo_destino' => 30, 'distancia' => 9.04],
            ['id' => 30, 'nodo_origen' => 30, 'nodo_destino' => 31, 'distancia' => 9.82],
            ['id' => 31, 'nodo_origen' => 31, 'nodo_destino' => 32, 'distancia' => 13.69],
            ['id' => 32, 'nodo_origen' => 32, 'nodo_destino' => 33, 'distancia' => 10.61],
            ['id' => 33, 'nodo_origen' => 33, 'nodo_destino' => 34, 'distancia' => 6.39],
            ['id' => 34, 'nodo_origen' => 34, 'nodo_destino' => 35, 'distancia' => 3.14],
            ['id' => 35, 'nodo_origen' => 35, 'nodo_destino' => 36, 'distancia' => 2.94],
            ['id' => 36, 'nodo_origen' => 36, 'nodo_destino' => 37, 'distancia' => 13.29],
            ['id' => 37, 'nodo_origen' => 37, 'nodo_destino' => 38, 'distancia' => 5.42],
            ['id' => 38, 'nodo_origen' => 38, 'nodo_destino' => 39, 'distancia' => 3.27],
            ['id' => 39, 'nodo_origen' => 39, 'nodo_destino' => 40, 'distancia' => 5.63],
            ['id' => 40, 'nodo_origen' => 40, 'nodo_destino' => 41, 'distancia' => 2.29],
            ['id' => 41, 'nodo_origen' => 41, 'nodo_destino' => 42, 'distancia' => 4.51],
            ['id' => 42, 'nodo_origen' => 42, 'nodo_destino' => 43, 'distancia' => 7.56],
            ['id' => 43, 'nodo_origen' => 43, 'nodo_destino' => 44, 'distancia' => 4.25],
            ['id' => 44, 'nodo_origen' => 44, 'nodo_destino' => 45, 'distancia' => 5.22],
            ['id' => 45, 'nodo_origen' => 45, 'nodo_destino' => 46, 'distancia' => 10.47],
            ['id' => 46, 'nodo_origen' => 46, 'nodo_destino' => 47, 'distancia' => 14.38],
            ['id' => 47, 'nodo_origen' => 47, 'nodo_destino' => 48, 'distancia' => 12.34],
            ['id' => 48, 'nodo_origen' => 48, 'nodo_destino' => 49, 'distancia' => 7.66],
            ['id' => 49, 'nodo_origen' => 49, 'nodo_destino' => 50, 'distancia' => 6.45],
            ['id' => 50, 'nodo_origen' => 50, 'nodo_destino' => 51, 'distancia' => 5.19],
            // Bloque D - acceso y pasillo piso 1
            ['id' => 51, 'nodo_origen' => 44, 'nodo_destino' => 52, 'distancia' => 19.8],
            ['id' => 52, 'nodo_origen' => 52, 'nodo_destino' => 53, 'distancia' => 2.5],
            ['id' => 53, 'nodo_origen' => 52, 'nodo_destino' => 54, 'distancia' => 1.5],
            ['id' => 54, 'nodo_origen' => 52, 'nodo_destino' => 55, 'distancia' => 1.0],
            ['id' => 55, 'nodo_origen' => 52, 'nodo_destino' => 56, 'distancia' => 5.0],
            ['id' => 56, 'nodo_origen' => 56, 'nodo_destino' => 57, 'distancia' => 8.0],
            ['id' => 57, 'nodo_origen' => 56, 'nodo_destino' => 58, 'distancia' => 15.0],
            // Bloque D - escaleras y piso 2
            ['id' => 58, 'nodo_origen' => 58, 'nodo_destino' => 59, 'distancia' => 16.0],
            ['id' => 59, 'nodo_origen' => 59, 'nodo_destino' => 60, 'distancia' => 1.0],
            ['id' => 60, 'nodo_origen' => 59, 'nodo_destino' => 61, 'distancia' => 5.5],
            ['id' => 61, 'nodo_origen' => 59, 'nodo_destino' => 62, 'distancia' => 6.5],
            ['id' => 62, 'nodo_origen' => 59, 'nodo_destino' => 63, 'distancia' => 7.0],
            ['id' => 63, 'nodo_origen' => 59, 'nodo_destino' => 64, 'distancia' => 5.0],
        ];
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

        // Crear la conexión en la tabla pivote (evita duplicados)
        $origen->vecinos()->syncWithoutDetaching([
            $destinoId => ['distancia' => $distancia]
        ]);

        // Si se pide, crea también la conexión inversa (opcional)
        if (!empty($data['bidireccional'])) {
            $destino = Nodo::findOrFail($destinoId);
            $destino->vecinos()->syncWithoutDetaching([
                $origen->id => ['distancia' => $distancia]
            ]);
        }

        return response()->json(['message' => 'Conexión creada'], 201);
    }

    /**
     * Buscar nodos por nombre
     * GET /api/nodos/buscar?q=cafeteria
     */
    public function buscar(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'error' => 'La búsqueda debe tener al menos 2 caracteres'
            ], 400);
        }

        $nodos = collect($this->defaultNodos());
        $resultados = $nodos->filter(function ($nodo) use ($query) {
            return stripos($nodo['nombre'], $query) !== false;
        })->values()->all();

        return response()->json([
            'query' => $query,
            'total' => count($resultados),
            'resultados' => $resultados
        ]);
    }

    /**
     * Encontrar el nodo más cercano a unas coordenadas
     * POST /api/nodos/mas-cercano
     * Body: {"latitud": 4.154, "longitud": -74.895}
     */
    public function masCercano(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        $nodos = collect($this->defaultNodos());
        $miLatitud = $validated['latitud'];
        $miLongitud = $validated['longitud'];

        $masC = null;
        $menorDistancia = PHP_INT_MAX;

        foreach ($nodos as $nodo) {
            $distancia = $this->distanciaHaversine(
                $miLatitud,
                $miLongitud,
                $nodo['latitud'],
                $nodo['longitud']
            );

            if ($distancia < $menorDistancia) {
                $menorDistancia = $distancia;
                $masC = $nodo;
                $masC['distancia'] = round($distancia, 2);
            }
        }

        return response()->json([
            'ubicacion_actual' => [
                'latitud' => $miLatitud,
                'longitud' => $miLongitud
            ],
            'nodo_mas_cercano' => $masC
        ]);
    }

    /**
     * Calcular ruta entre dos nodos usando Dijkstra
     * GET /api/nodos/ruta/{origen}/{destino}
     */
    public function ruta(int $origen, int $destino): JsonResponse
    {
        $nodos = collect($this->defaultNodos())->keyBy('id')->toArray();
        $conexiones = $this->defaultConexiones();

        // Validar que los nodos existan
        if (!isset($nodos[$origen]) || !isset($nodos[$destino])) {
            return response()->json([
                'error' => 'Nodo no encontrado'
            ], 404);
        }

        // Construir grafo de conexiones
        $grafo = [];
        foreach ($conexiones as $conn) {
            if (!isset($grafo[$conn['nodo_origen']])) {
                $grafo[$conn['nodo_origen']] = [];
            }
            $grafo[$conn['nodo_origen']][] = [
                'destino' => $conn['nodo_destino'],
                'distancia' => $conn['distancia']
            ];
            // Conexión inversa (bidireccional)
            if (!isset($grafo[$conn['nodo_destino']])) {
                $grafo[$conn['nodo_destino']] = [];
            }
            $grafo[$conn['nodo_destino']][] = [
                'destino' => $conn['nodo_origen'],
                'distancia' => $conn['distancia']
            ];
        }

        // Algoritmo de Dijkstra
        $distancias = [];
        $padres = [];
        $visitados = [];

        foreach ($nodos as $id => $nodo) {
            $distancias[$id] = ($id == $origen) ? 0 : PHP_INT_MAX;
            $padres[$id] = null;
        }

        for ($i = 0; $i < count($nodos); $i++) {
            $nodoActual = null;
            $menorDist = PHP_INT_MAX;

            foreach ($distancias as $id => $dist) {
                if (!isset($visitados[$id]) && $dist < $menorDist) {
                    $menorDist = $dist;
                    $nodoActual = $id;
                }
            }

            if ($nodoActual === null || $menorDist === PHP_INT_MAX) break;

            $visitados[$nodoActual] = true;

            if (isset($grafo[$nodoActual])) {
                foreach ($grafo[$nodoActual] as $vecino) {
                    $vecinoId = $vecino['destino'];
                    $nuevaDist = $distancias[$nodoActual] + $vecino['distancia'];

                    if ($nuevaDist < $distancias[$vecinoId]) {
                        $distancias[$vecinoId] = $nuevaDist;
                        $padres[$vecinoId] = $nodoActual;
                    }
                }
            }
        }

        // Reconstruir ruta
        if ($distancias[$destino] === PHP_INT_MAX) {
            return response()->json([
                'error' => 'No hay ruta disponible entre estos nodos'
            ], 404);
        }

        $rutaIds = [];
        $nodoActual = $destino;
        while ($nodoActual !== null) {
            array_unshift($rutaIds, $nodoActual);
            $nodoActual = $padres[$nodoActual];
        }

        // Construir respuesta con detalles
        $rutaDetallada = [];
        foreach ($rutaIds as $id) {
            $rutaDetallada[] = $nodos[$id];
        }

        return response()->json([
            'origen' => $nodos[$origen],
            'destino' => $nodos[$destino],
            'distancia_total' => round($distancias[$destino], 2),
            'pasos' => count($rutaIds) - 1,
            'ruta' => $rutaDetallada
        ]);
    }

    /**
     * Calcular distancia entre dos puntos usando fórmula de Haversine
     */
    private function distanciaHaversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $radioTierra = 6371000; // en metros

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radioTierra * $c; // en metros
    }
}
