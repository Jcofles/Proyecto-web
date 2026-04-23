<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar que todos los usuarios tengan un status_id válido
        $activoId = DB::table('user_status')->where('nombre', 'activo')->value('id');
        
        // Actualizar usuarios sin status_id
        DB::table('users')
            ->whereNull('status_id')
            ->update(['status_id' => $activoId]);
    }

    public function down(): void
    {
        // No hacer nada en rollback para preservar datos
    }
};
