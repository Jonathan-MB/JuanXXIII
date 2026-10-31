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
        //  La lista de tareas es la misma para todos los retiros. El día se
        // guarda relativo al viernes: 0 viernes, 1 sábado, 2 domingo,
        // y los negativos son días antes del retiro.
        Schema::create('tarea_cronogramas', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('dia_relativo');
            $table->time('hora');
            $table->string('descripcion',200);
            $table->string('responsable',60)->nullable();

            $table->timestamps();

        });

        // Qué tareas se marcaron como hechas en cada retiro, quién y cuándo
        Schema::create('tarea_cumplidas', function (Blueprint $table) {
            $table->id();
            $table->timestamp('hecha_en');

            $table->foreignId('tarea_cronograma_id')->constrained('tarea_cronogramas')->cascadeOnDelete();
            $table->foreignId('retiro_id')->constrained('retiros')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->timestamps();

            $table->unique(['tarea_cronograma_id', 'retiro_id']);

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea_cumplidas');
        Schema::dropIfExists('tarea_cronogramas');
    }
};
