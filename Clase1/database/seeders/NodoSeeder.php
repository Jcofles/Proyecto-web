<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Nodo;

class NodoSeeder extends Seeder
{
    /**
     * Seed the application's nodos and conexiones for Bloque D.
     */
    public function run(): void
    {
        if (DB::table('nodos')->count() > 0) {
            return;
        }

        // Limpiar tablas (se eliminan conexiones por cascada al borrar nodos)
        DB::table('conexiones')->delete();
        DB::table('nodos')->delete();

        // Obtener IDs de tipos
        $tipoSalon = DB::table('nodo_tipos')->where('nombre', 'salon')->value('id');
        $tipoBano = DB::table('nodo_tipos')->where('nombre', 'baño')->value('id');
        $tipoEscaleras = DB::table('nodo_tipos')->where('nombre', 'escaleras')->value('id');

        // Coordenadas ITFIP (Espinal, Tolima) — centradas en 4.148, -74.885
        // Los nodos están separados por 0.0001 grados (~10m) para que queden juntos
        $salon101 = Nodo::create([
            'nombre' => 'Salón 101',
            'latitud' => 4.14800000,
            'longitud' => -74.88500000,
            'tipo_id' => $tipoSalon,
            'piso' => 1,
        ]);

        $banos = Nodo::create([
            'nombre' => 'Baños Bloque D',
            'latitud' => 4.14800100,
            'longitud' => -74.88500000,
            'tipo_id' => $tipoBano,
            'piso' => 1,
        ]);

        $salon102 = Nodo::create([
            'nombre' => 'Salón 102',
            'latitud' => 4.14800000,
            'longitud' => -74.88500100,
            'tipo_id' => $tipoSalon,
            'piso' => 1,
        ]);

        $escaleras = Nodo::create([
            'nombre' => 'Escaleras Bloque D',
            'latitud' => 4.14800100,
            'longitud' => -74.88500100,
            'tipo_id' => $tipoEscaleras,
            'piso' => 1,
        ]);

        // Conexiones (bidireccionales) — distancia en metros (float)
        $salon101->vecinos()->attach($banos->id, ['distancia' => 8.5]);
        $banos->vecinos()->attach($salon101->id, ['distancia' => 8.5]);

        $salon101->vecinos()->attach($salon102->id, ['distancia' => 15.2]);
        $salon102->vecinos()->attach($salon101->id, ['distancia' => 15.2]);

        $salon102->vecinos()->attach($escaleras->id, ['distancia' => 6.4]);
        $escaleras->vecinos()->attach($salon102->id, ['distancia' => 6.4]);

        $banos->vecinos()->attach($escaleras->id, ['distancia' => 4.8]);
        $escaleras->vecinos()->attach($banos->id, ['distancia' => 4.8]);

        // Conexión alternativa directa
        $salon101->vecinos()->attach($escaleras->id, ['distancia' => 12.0]);
        $escaleras->vecinos()->attach($salon101->id, ['distancia' => 12.0]);
    }
}
