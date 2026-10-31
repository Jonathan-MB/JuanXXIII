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
        //  El horario base: se arma una vez y se usa en todos los retiros.
        // Cada fila es una franja de media hora. El día es relativo al
        // viernes (0 viernes, 1 sábado, 2 domingo).
        Schema::create('santisimo_bases', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('dia_relativo');
            $table->time('hora_inicio');
            $table->boolean('todos')->default(false);
            $table->string('actividad',60)->nullable();

            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();

            $table->timestamps();

            $table->unique(['dia_relativo', 'hora_inicio']);

        });

        //  La copia de cada retiro: se edita según la necesidad del
        // momento sin tocar la base.
        Schema::create('santisimo_franjas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('inicio');
            $table->boolean('todos')->default(false);
            $table->string('actividad',60)->nullable();

            $table->foreignId('retiro_id')->constrained('retiros')->cascadeOnDelete();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();

            $table->timestamps();

            $table->unique(['retiro_id', 'inicio']);

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santisimo_franjas');
        Schema::dropIfExists('santisimo_bases');
    }
};
