<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite no soporta MODIFY COLUMN, usar recrear tabla
        if (DB::getDriverName() === 'sqlite') {
            // Para SQLite, recrear la tabla
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['activo', 'inactivo', 'bloqueado', 'eliminado'])->default('activo');
            });
        } else {
            // Para MySQL
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('activo', 'inactivo', 'bloqueado', 'eliminado') DEFAULT 'activo'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['activo', 'inactivo'])->default('activo');
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('activo', 'inactivo') DEFAULT 'activo'");
        }
    }
};
