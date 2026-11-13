<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  De quién es la foto de fondo. Algunas licencias de internet piden
    // nombrar al autor; sale en letra pequeña en el carrusel.
    public function up(): void
    {
        Schema::table('perseverancias', function (Blueprint $table) {
            $table->string('imagen_credito', 200)->nullable()->after('imagen');
        });
    }



    public function down(): void
    {
        Schema::table('perseverancias', function (Blueprint $table) {
            $table->dropColumn('imagen_credito');
        });
    }
};
