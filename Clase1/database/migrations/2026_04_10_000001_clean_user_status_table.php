<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero actualizar usuarios con status_id inválidos a 1 (activo)
        DB::table('users')
            ->whereNotIn('status_id', [1, 2, 3, 4])
            ->orWhereNull('status_id')
            ->update(['status_id' => 1]);
        
        // Desactivar foreign key checks temporalmente en MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        // Eliminar registros bugueados
        DB::table('user_status')->whereNotIn('id', [1, 2, 3, 4])->delete();
        
        // Asegurar que existen los 4 estados correctos
        DB::table('user_status')->updateOrInsert(
            ['id' => 1],
            ['nombre' => 'activo', 'descripcion' => 'Usuario activo en el sistema', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        
        DB::table('user_status')->updateOrInsert(
            ['id' => 2],
            ['nombre' => 'inactivo', 'descripcion' => 'Usuario inactivo', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        
        DB::table('user_status')->updateOrInsert(
            ['id' => 3],
            ['nombre' => 'bloqueado', 'descripcion' => 'Usuario bloqueado por administrador', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        
        DB::table('user_status')->updateOrInsert(
            ['id' => 4],
            ['nombre' => 'eliminado', 'descripcion' => 'Usuario eliminado', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        
        // Reactivar foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void
    {
        // No hacer nada en rollback
    }
};
