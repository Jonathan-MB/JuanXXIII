<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cada equipo lleva su propio inventario
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',80);
            $table->string('unidad',20)->default('unidades');
            $table->unsignedInteger('cantidad')->default(0);
            $table->unsignedInteger('por_comprar')->default(0);
            $table->string('estado',60)->nullable();

            $table->foreignId('equipo_id')->constrained('equipos')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['equipo_id', 'nombre']);

        });

        //  Cada entrada o salida queda registrada con quién la hizo,
        // para saber por qué cambió una cantidad.
        Schema::create('movimiento_inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',10);
            $table->unsignedInteger('cantidad');
            $table->unsignedInteger('cantidad_resultante');
            $table->string('nota',255)->nullable();

            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventarios');
        Schema::dropIfExists('articulos');
    }
};
