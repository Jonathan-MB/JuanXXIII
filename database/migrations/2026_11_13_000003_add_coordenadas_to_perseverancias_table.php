<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  El punto de cada perseverancia en el mapa del inicio. Sin
    // coordenadas no sale en el mapa (sí en la lista y el carrusel).
    public function up(): void
    {
        Schema::table('perseverancias', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('mapa');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });
    }



    public function down(): void
    {
        Schema::table('perseverancias', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};
