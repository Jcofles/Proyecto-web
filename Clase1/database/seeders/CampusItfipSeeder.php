<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusItfipSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('nodos')->count() > 0) {
            return;
        }

        // Limpiar tablas
        DB::table('conexiones')->delete();
        DB::table('nodos')->delete();

        // Obtener IDs de tipos
        $tipoPaso = DB::table('nodo_tipos')->where('nombre', 'pasillo')->value('id');
        $tipoEscalera = DB::table('nodo_tipos')->where('nombre', 'escaleras')->value('id');

        // Insertar nodos del campus
        $nodos = [
            [1, 4.1540264, -74.8956435, 'Entrada Principal', $tipoPaso, 1],
            [2, 4.1540938, -74.8957258, 'Paso 2', $tipoPaso, 1],
            [3, 4.1541906, -74.8957731, 'Paso 3', $tipoPaso, 1],
            [4, 4.1542659, -74.8958503, 'Paso 4', $tipoPaso, 1],
            [5, 4.1543186, -74.8958941, 'Paso 5', $tipoPaso, 1],
            [6, 4.1544408, -74.8959701, 'Paso 6', $tipoPaso, 1],
            [7, 4.1544413, -74.8960247, 'Paso 7', $tipoPaso, 1],
            [8, 4.1545834, -74.8960978, 'Paso 8', $tipoPaso, 1],
            [9, 4.1546025, -74.8961426, 'Paso 9', $tipoPaso, 1],
            [10, 4.1546625, -74.8961154, 'Paso 10', $tipoPaso, 1],
            [11, 4.1546793, -74.896101, 'Paso 11', $tipoPaso, 1],
            [12, 4.1547632, -74.8962123, 'Paso 12', $tipoPaso, 1],
            [13, 4.1547859, -74.8963001, 'Paso 13', $tipoPaso, 1],
            [14, 4.1549386, -74.8963443, 'Paso 14', $tipoPaso, 1],
            [15, 4.1550525, -74.8964256, 'Paso 15', $tipoPaso, 1],
            [16, 4.1551739, -74.8964791, 'Paso 16', $tipoPaso, 1],
            [17, 4.155233, -74.8965643, 'Punto Central', $tipoPaso, 1],
            [18, 4.155224, -74.8965652, 'Paso 18', $tipoPaso, 1],
            [19, 4.1552844, -74.8965886, 'Paso 19', $tipoPaso, 1],
            [20, 4.155359, -74.896688, 'Paso 20', $tipoPaso, 1],
            [21, 4.1554198, -74.8967654, 'Paso 21', $tipoPaso, 1],
            [22, 4.155476, -74.8967857, 'Paso 22', $tipoPaso, 1],
            [23, 4.1555267, -74.8968303, 'Paso 23', $tipoPaso, 1],
            [24, 4.1555791, -74.8968965, 'Paso 24', $tipoPaso, 1],
            [25, 4.155652, -74.8970147, 'Paso 25', $tipoPaso, 1],
            [26, 4.1557584, -74.8970683, 'Paso 26', $tipoPaso, 1],
            [27, 4.1558265, -74.897036, 'Paso 27', $tipoPaso, 1],
            [28, 4.1558498, -74.8970607, 'Paso 28', $tipoPaso, 1],
            [29, 4.1558414, -74.8971355, 'Paso 29', $tipoPaso, 1],
            [30, 4.1559252, -74.8971802, 'Paso 30', $tipoPaso, 1],
            [31, 4.1560003, -74.8972495, 'Paso 31', $tipoPaso, 1],
            [32, 4.1561003, -74.8973548, 'Paso 32', $tipoPaso, 1],
            [33, 4.1561966, -74.8973681, 'Paso 33', $tipoPaso, 1],
            [34, 4.156235, -74.8974276, 'Paso 34', $tipoPaso, 1],
            [35, 4.1562282, -74.8974544, 'Paso 35', $tipoPaso, 1],
            [36, 4.1562462, -74.8974778, 'Paso 36', $tipoPaso, 1],
            [37, 4.1563595, -74.8975361, 'Paso 37', $tipoPaso, 1],
            [38, 4.1563803, -74.8975816, 'Paso 38', $tipoPaso, 1],
            [39, 4.1563901, -74.897611, 'Paso 39', $tipoPaso, 1],
            [40, 4.1564351, -74.8976045, 'Paso 40', $tipoPaso, 1],
            [41, 4.1564529, -74.8976187, 'Paso 41', $tipoPaso, 1],
            [42, 4.1564466, -74.8976613, 'Paso 42', $tipoPaso, 1],
            [43, 4.1565078, -74.8977214, 'Paso 43', $tipoPaso, 1],
            [44, 4.1565336, -74.8977338, 'Entrada Bloque 2', $tipoPaso, 1],
            [45, 4.1565693, -74.8977425, 'Paso 45', $tipoPaso, 1],
            [46, 4.1566646, -74.8977713, 'Paso 46', $tipoPaso, 1],
            [47, 4.1567974, -74.8977537, 'Paso 47', $tipoPaso, 1],
            [48, 4.1569201, -74.8977132, 'Paso 48', $tipoPaso, 1],
            [49, 4.1569299, -74.8976371, 'Entrada Cafetería', $tipoPaso, 1],
            [50, 4.1569081, -74.8975829, 'Paso Cafetería', $tipoPaso, 1],
            [51, 4.1569111, -74.8976424, 'Escalera Cafetería', $tipoEscalera, 1],
        ];

        foreach ($nodos as $nodo) {
            DB::table('nodos')->insert([
                'id' => $nodo[0],
                'latitud' => $nodo[1],
                'longitud' => $nodo[2],
                'nombre' => $nodo[3],
                'tipo_id' => $nodo[4],
                'piso' => $nodo[5],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear conexiones secuenciales (cada nodo conecta con el siguiente)
        for ($i = 1; $i < 51; $i++) {
            $n1 = DB::table('nodos')->where('id', $i)->first();
            $n2 = DB::table('nodos')->where('id', $i + 1)->first();
            
            if ($n1 && $n2) {
                $distancia = $this->calcularDistancia($n1->latitud, $n1->longitud, $n2->latitud, $n2->longitud);
                
                DB::table('conexiones')->insert([
                    'nodo_origen_id' => $i,
                    'nodo_destino_id' => $i + 1,
                    'distancia' => $distancia,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return round($earthRadius * $c, 2);
    }
}
