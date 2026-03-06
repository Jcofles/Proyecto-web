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
        Schema::create('user_status', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar valores iniciales
        DB::table('user_status')->insert([
            ['nombre' => 'activo', 'descripcion' => 'Usuario activo en el sistema', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'inactivo', 'descripcion' => 'Usuario inactivo', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'bloqueado', 'descripcion' => 'Usuario bloqueado por administrador', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'eliminado', 'descripcion' => 'Usuario eliminado', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Agregar columna temporal
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('status');
        });

        // Migrar datos existentes
        $statuses = DB::table('user_status')->get()->keyBy('nombre');
        DB::table('users')->get()->each(function ($user) use ($statuses) {
            $statusId = $statuses[$user->status]->id ?? $statuses['activo']->id;
            DB::table('users')->where('id', $user->id)->update(['status_id' => $statusId]);
        });

        // Eliminar columna enum y hacer status_id obligatorio
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable(false)->change();
            $table->foreign('status_id')->references('id')->on('user_status')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->enum('status', ['activo', 'inactivo', 'bloqueado', 'eliminado'])->default('activo')->after('status_id');
        });

        // Migrar datos de vuelta
        DB::table('users')->get()->each(function ($user) {
            $status = DB::table('user_status')->where('id', $user->status_id)->value('nombre');
            if ($status) {
                DB::table('users')->where('id', $user->id)->update(['status' => $status]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });

        Schema::dropIfExists('user_status');
    }
};
