<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla de catálogo
        Schema::create('nodo_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar valores iniciales
        DB::table('nodo_tipos')->insert([
            ['nombre' => 'salon', 'descripcion' => 'Salón de clases', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'pasillo', 'descripcion' => 'Pasillo o corredor', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'baño', 'descripcion' => 'Baño o sanitario', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'escaleras', 'descripcion' => 'Escaleras', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Agregar columna temporal para migrar datos
        Schema::table('nodos', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_id')->nullable()->after('tipo');
        });

        // Migrar datos existentes
        $tipos = DB::table('nodo_tipos')->get()->keyBy('nombre');
        DB::table('nodos')->get()->each(function ($nodo) use ($tipos) {
            $tipoId = $tipos[$nodo->tipo]->id ?? null;
            if ($tipoId) {
                DB::table('nodos')->where('id', $nodo->id)->update(['tipo_id' => $tipoId]);
            }
        });

        // Eliminar columna enum y hacer tipo_id obligatorio
        Schema::table('nodos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('nodos', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_id')->nullable(false)->change();
            $table->foreign('tipo_id')->references('id')->on('nodo_tipos')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('nodos', function (Blueprint $table) {
            $table->dropForeign(['tipo_id']);
            $table->enum('tipo', ['salon', 'pasillo', 'baño', 'escaleras'])->after('tipo_id');
        });

        // Migrar datos de vuelta
        DB::table('nodos')->get()->each(function ($nodo) {
            $tipo = DB::table('nodo_tipos')->where('id', $nodo->tipo_id)->value('nombre');
            if ($tipo) {
                DB::table('nodos')->where('id', $nodo->id)->update(['tipo' => $tipo]);
            }
        });

        Schema::table('nodos', function (Blueprint $table) {
            $table->dropColumn('tipo_id');
        });

        Schema::dropIfExists('nodo_tipos');
    }
};
