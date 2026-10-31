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
        //  Las mesas son de cada retiro y de cada sexo: la mesa 1 de
        // hombres y la mesa 1 de mujeres son mesas distintas.
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('sexo',10);
            $table->unsignedTinyInteger('numero');
            $table->string('nombre',60)->nullable();
            $table->unsignedTinyInteger('cantidad_sillas')->default(8);

            // CT y CA: los dos catequistas que supervisan la mesa
            $table->string('catequista_titular',100)->nullable();
            $table->string('catequista_auxiliar',100)->nullable();

            $table->foreignId('retiro_id')->constrained('retiros')->restrictOnDelete();

            $table->timestamps();

            $table->unique(['retiro_id', 'sexo', 'numero']);

        });

        Schema::table('inscripcions', function (Blueprint $table) {
            $table->unsignedTinyInteger('silla')->nullable()->after('declara_requisitos');
            $table->timestamp('llegada_en')->nullable()->after('silla');

            $table->foreignId('mesa_id')->nullable()->after('retiro_id')->constrained('mesas')->nullOnDelete();

            // Dos personas nunca pueden quedar en la misma silla
            $table->unique(['mesa_id', 'silla']);
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripcions', function (Blueprint $table) {
            $table->dropUnique(['mesa_id', 'silla']);
            $table->dropConstrainedForeignId('mesa_id');
            $table->dropColumn(['silla', 'llegada_en']);
        });

        Schema::dropIfExists('mesas');
    }
};
