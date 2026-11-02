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
        //  Un equipo que no participa solo cubre sus horas fijas; el resto
        // del horario se reparte entre los que participan.
        Schema::table('equipos', function (Blueprint $table) {
            $table->boolean('participa_santisimo')->default(true)->after('cantidad_personas');
        });

        //  Reglas del reparto automático: horas fijas de un equipo u horas
        // en las que no puede ir. Sin día, la regla vale todos los días.
        Schema::create('santisimo_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',10);
            $table->tinyInteger('dia_relativo')->nullable();
            $table->time('hora_desde');
            $table->time('hora_hasta');

            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santisimo_reglas');

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('participa_santisimo');
        });
    }
};
