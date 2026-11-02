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
        //  Quienes vivieron el retiro antes de usar la aplicación. Vienen de
        // los Excel viejos, que no traen todos los datos de una inscripción:
        // por eso van aparte y no en personas.
        Schema::create('retirista_anteriors', function (Blueprint $table) {
            $table->id();
            $table->string('cedula',20)->unique();
            $table->string('nombre',120);
            $table->string('sexo',10)->nullable();
            $table->unsignedSmallInteger('numero_retiro')->nullable();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('celular',20)->nullable();

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retirista_anteriors');
    }
};
