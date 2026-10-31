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
        Schema::create('inscripcions', function (Blueprint $table) {
            $table->id();

            //  inscrito, confirmado, llego, termino, abandono, no_asistio.
            // Nunca se borra una inscripción: se cambia el estado.
            $table->string('estado',15)->default('inscrito');
            $table->string('forma_pago',120);
            $table->string('comprobante',255)->nullable();
            $table->boolean('tiene_padrino')->default(false);
            $table->string('padrino_nombre',100)->nullable();
            $table->string('padrino_telefono',20)->nullable();
            $table->string('padrino_presente',60)->nullable();
            $table->string('padrino_parroquia',150)->nullable();
            $table->boolean('acepta_datos')->default(false);
            $table->boolean('declara_requisitos')->default(false);

            $table->foreignId('persona_id')->constrained('personas')->restrictOnDelete();
            $table->foreignId('retiro_id')->constrained('retiros')->restrictOnDelete();

            $table->timestamps();

            // Una persona se inscribe una sola vez en cada retiro
            $table->unique(['persona_id', 'retiro_id']);

        });

        Schema::create('contacto_emergencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            $table->string('celular',20);
            $table->string('telefono_fijo',20)->nullable();
            $table->string('correo',100)->nullable();
            $table->string('parentesco',45);

            $table->foreignId('inscripcion_id')->constrained('inscripcions')->cascadeOnDelete();

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacto_emergencias');
        Schema::dropIfExists('inscripcions');
    }
};
