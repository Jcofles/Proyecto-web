<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        $validStatusIds = DB::table('user_status')
            ->whereIn('nombre', array_keys($states))
            ->pluck('id')
            ->toArray();

        $activeStatusId = DB::table('user_status')
            ->where('nombre', 'activo')
            ->value('id');

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

        if (Schema::hasTable('pending_users')) {
            // Limpiar registros pendientes para evitar bloqueos por FK.
            DB::table('pending_users')->delete();
        }

        if (Schema::hasTable('users')) {
            DB::table('users')
                ->whereNull('status_id')
                ->orWhereNotIn('status_id', $validStatusIds)
                ->update(['status_id' => $activeStatusId]);

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
                DB::table('users')->delete();
            }
        }

        DB::table('user_status')->whereNotIn('nombre', array_keys($states))->delete();
    }

    public function down(): void
    {
        // No restaurar datos eliminados automáticamente.
    }
};
