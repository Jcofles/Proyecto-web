<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Limpiar cualquier estado inválido
        DB::table('user_status')->whereNotIn('nombre', ['activo', 'inactivo', 'bloqueado', 'eliminado'])->delete();

        // Obtener el ID actual del estado 'activo'
        $currentActiveId = DB::table('user_status')->where('nombre', 'activo')->value('id');

        if ($currentActiveId !== 1) {
            // Si no es 1, necesitamos reordenar
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Crear temporalmente los estados con IDs correctos
            $tempStates = [
                1 => ['nombre' => 'activo', 'descripcion' => 'Usuario activo en el sistema'],
                2 => ['nombre' => 'inactivo', 'descripcion' => 'Usuario inactivo'],
                3 => ['nombre' => 'bloqueado', 'descripcion' => 'Usuario bloqueado por administrador'],
                4 => ['nombre' => 'eliminado', 'descripcion' => 'Usuario eliminado'],
            ];

            // Eliminar todos los estados existentes
            DB::table('user_status')->delete();

            // Insertar con IDs correctos
            foreach ($tempStates as $id => $state) {
                DB::table('user_status')->insert([
                    'id' => $id,
                    'nombre' => $state['nombre'],
                    'descripcion' => $state['descripcion'],
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Agregar status_id a pending_users si no existe
        if (Schema::hasTable('pending_users') && !Schema::hasColumn('pending_users', 'status_id')) {
            Schema::table('pending_users', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable()->after('email_verification_expires_at');
            });
        }

        // Asegurar que pending_users tenga status_id válido (usar 'inactivo' por defecto para pendientes)
        if (Schema::hasTable('pending_users')) {
            $inactivoId = DB::table('user_status')->where('nombre', 'inactivo')->value('id') ?? 2;
            DB::table('pending_users')
                ->whereNull('status_id')
                ->orWhereNotIn('status_id', [1, 2, 3, 4])
                ->update(['status_id' => $inactivoId]);
        }

        // Asegurar que users tengan status_id válido
        $activoId = DB::table('user_status')->where('nombre', 'activo')->value('id');
        DB::table('users')
            ->whereNull('status_id')
            ->orWhereNotIn('status_id', [1, 2, 3, 4])
            ->update(['status_id' => $activoId]);
    }

    public function down(): void
    {
        // No hacer rollback automático
    }
};