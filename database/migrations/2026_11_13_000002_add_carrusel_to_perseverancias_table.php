<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  Las perseverancias se editan en su propia sección y salen en el
    // carrusel del inicio: el sector ("El Rodeo"), lo que se hace ese día
    // (rosario, eucaristía…), una imagen de fondo, si se muestra o no y
    // cómo buscarla en el mapa si la dirección sola no basta.
    public function up(): void
    {
        Schema::table('perseverancias', function (Blueprint $table) {
            $table->string('sector', 60)->nullable()->after('tipo');
            $table->string('mapa', 160)->nullable()->after('direccion');
            $table->text('actividades')->nullable()->after('mapa');
            $table->string('imagen')->nullable()->after('actividades');
            $table->boolean('en_inicio')->default(true)->after('imagen');
        });
    }



    public function down(): void
    {
        Schema::table('perseverancias', function (Blueprint $table) {
            $table->dropColumn(['sector', 'mapa', 'actividades', 'imagen', 'en_inicio']);
        });
    }
};
