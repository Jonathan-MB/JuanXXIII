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
        //  El "Desarrollo del retiro" base, uno por sexo (el de mujeres va
        // más temprano). El día es relativo al viernes; lo que pasa
        // después de medianoche sigue siendo de la jornada anterior.
        Schema::create('actividad_bases', function (Blueprint $table) {
            $table->id();
            $table->string('sexo',10);
            $table->tinyInteger('dia_relativo');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->string('nombre',100);
            $table->boolean('es_charla')->default(false);
            $table->timestamps();

            $table->index(['sexo', 'dia_relativo']);

        });

        //  La copia de cada retiro, con fechas reales y el nombre de quien
        // da cada charla.
        Schema::create('actividad_retiros', function (Blueprint $table) {
            $table->id();
            $table->string('sexo',10);
            $table->dateTime('inicio');
            $table->dateTime('fin')->nullable();
            $table->string('nombre',100);
            $table->boolean('es_charla')->default(false);
            $table->string('charlista',100)->nullable();

            $table->foreignId('retiro_id')->constrained('retiros')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['retiro_id', 'sexo', 'inicio']);

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_retiros');
        Schema::dropIfExists('actividad_bases');
    }
};
