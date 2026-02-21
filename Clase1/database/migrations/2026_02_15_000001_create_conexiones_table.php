<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conexiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nodo_origen_id')->constrained('nodos')->onDelete('cascade');
            $table->foreignId('nodo_destino_id')->constrained('nodos')->onDelete('cascade');
            $table->float('distancia');
            $table->timestamps();

            $table->unique(['nodo_origen_id', 'nodo_destino_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conexiones');
    }
};
