<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que todos los estados válidos existen
        $states = [
            'activo' => 'Usuario activo en el sistema',
            'inactivo' => 'Usuario inactivo',
            'bloqueado' => 'Usuario bloqueado por administrador',
            'eliminado' => 'Usuario eliminado',
        ];

        foreach ($states as $name => $description) {
            DB::table('user_status')->updateOrInsert(
                ['nombre' => $name],
                ['descripcion' => $description, 'activo' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $validStatusIds = DB::table('user_status')->whereIn('nombre', array_keys($states))->pluck('id')->toArray();
        $activeStatusId = DB::table('user_status')->where('nombre', 'activo')->value('id');

        if ($activeStatusId === null) {
            $activeStatusId = DB::table('user_status')->insertGetId([
                'nombre' => 'activo',
                'descripcion' => 'Usuario activo en el sistema',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $validStatusIds[] = $activeStatusId;
        }

        // Normalizar usuarios a un estado válido antes de limpiar catálogos
        DB::table('users')
            ->whereNull('status_id')
            ->orWhereNotIn('status_id', $validStatusIds)
            ->update(['status_id' => $activeStatusId]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        DB::table('user_status')->whereNotIn('nombre', array_keys($states))->delete();
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Preservar solo los usuarios que tienen nodos asociados
        $userIdsWithNodos = DB::table('nodos')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if (!empty($userIdsWithNodos)) {
            DB::table('users')
                ->whereNotIn('id', $userIdsWithNodos)
                ->delete();
        } else {
            // Si no hay nodos asociados, eliminar todos los usuarios para limpiar la tabla
            DB::table('users')->delete();
        }
    }

    public function down(): void
    {
        // No restaurar datos eliminados automáticamente.
    }
};
