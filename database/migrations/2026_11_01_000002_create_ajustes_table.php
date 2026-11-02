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
        //  Ajustes generales que cambian sin tocar el código: el enlace del
        // grupo de WhatsApp y el correo de confirmación.
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->string('clave',60)->unique();
            $table->text('valor')->nullable();

            $table->timestamps();

        });

        // Cuándo se le mandó a la persona el correo de confirmación de cupo
        Schema::table('inscripcions', function (Blueprint $table) {
            $table->dateTime('confirmacion_enviada')->nullable()->after('declara_requisitos');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripcions', function (Blueprint $table) {
            $table->dropColumn('confirmacion_enviada');
        });

        Schema::dropIfExists('ajustes');
    }
};
