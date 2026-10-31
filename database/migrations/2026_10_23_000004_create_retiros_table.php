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
        //  Un registro es un fin de semana: hombres y mujeres van juntos
        // en fechas, pero cada uno lleva su propio número de retiro.
        Schema::create('retiros', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('numero_hombres')->unique();
            $table->unsignedSmallInteger('numero_mujeres')->unique();
            $table->date('fecha_inicio');
            $table->time('hora_inicio')->default('17:30:00');
            $table->date('fecha_fin');
            $table->time('hora_fin')->default('16:00:00');
            $table->decimal('ofrenda', 10, 2)->default(0);
            $table->string('que_incluye',150)->nullable();
            $table->unsignedTinyInteger('porcentaje_reserva')->default(50);
            $table->date('fecha_limite_pago')->nullable();
            $table->string('lugar',100);
            $table->string('direccion',100)->nullable();
            $table->string('ciudad',45)->default('Santiago de Cali');
            $table->boolean('inscripciones_abiertas')->default(false);

            $table->timestamps();

        });

        Schema::create('cuenta_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('entidad',45)->default('Nequi');
            $table->string('numero',20);
            $table->string('titular',60);

            $table->foreignId('retiro_id')->constrained('retiros')->cascadeOnDelete();

            $table->timestamps();

        });

        Schema::create('contacto_informes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',60);
            $table->string('celular',20);

            $table->foreignId('retiro_id')->constrained('retiros')->cascadeOnDelete();

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacto_informes');
        Schema::dropIfExists('cuenta_pagos');
        Schema::dropIfExists('retiros');
    }
};
