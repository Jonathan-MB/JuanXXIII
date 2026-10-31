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
        //  Una fila por cédula. Si alguien se inscribe en un retiro y no
        // asiste, en el siguiente se reutiliza la misma persona.
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombres',60);
            $table->string('apellidos',60);
            $table->string('cedula',20)->unique();
            $table->string('sexo',10);
            $table->date('fecha_nacimiento');
            $table->string('correo',100)->nullable();
            $table->string('celular',20);
            $table->string('direccion',100);
            $table->string('barrio',60);
            $table->string('ciudad',60);
            $table->string('eps',60);
            $table->string('medicamentos',255)->nullable();
            $table->string('talla_camiseta',5);
            $table->boolean('bautismo')->default(false);
            $table->boolean('primera_comunion')->default(false);
            $table->boolean('confirmacion')->default(false);
            $table->boolean('matrimonio')->default(false);

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
