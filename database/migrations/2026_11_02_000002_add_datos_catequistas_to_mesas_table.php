<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  El directorio lleva el celular, la ciudad y el barrio de los dos
    // catequistas de cada mesa. Los escriben a mano en la hoja de mesa en
    // blanco y Secretaría los pasa a Mesas.
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->string('titular_celular', 20)->nullable()->after('catequista_titular');
            $table->string('titular_ciudad', 60)->nullable()->after('titular_celular');
            $table->string('titular_barrio', 60)->nullable()->after('titular_ciudad');
            $table->string('auxiliar_celular', 20)->nullable()->after('catequista_auxiliar');
            $table->string('auxiliar_ciudad', 60)->nullable()->after('auxiliar_celular');
            $table->string('auxiliar_barrio', 60)->nullable()->after('auxiliar_ciudad');
        });
    }



    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn([
                'titular_celular',
                'titular_ciudad',
                'titular_barrio',
                'auxiliar_celular',
                'auxiliar_ciudad',
                'auxiliar_barrio',
            ]);
        });
    }
};
