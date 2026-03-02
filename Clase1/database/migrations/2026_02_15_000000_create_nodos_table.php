<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('nodos', function (Blueprint $table) {
        $table->id();
        
        // El cambio clave está aquí: añadimos nullable()
        $table->foreignId('user_id')
              ->nullable() 
              ->constrained('users')
              ->onDelete('cascade');
              
        $table->string('nombre');
        $table->decimal('latitud', 10, 8);
        $table->decimal('longitud', 11, 8);
        $table->enum('tipo', ['salon', 'pasillo', 'baño', 'escaleras']);
        $table->integer('piso');
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('nodos');
    }
};
