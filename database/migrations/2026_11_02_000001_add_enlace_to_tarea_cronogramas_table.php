<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  A dónde lleva la tarea (una sección o una impresión). Se guarda la
    // clave de TareaCronograma::ENLACES, no la dirección, para que siga
    // sirviendo si la aplicación cambia de servidor.
    public function up(): void
    {
        Schema::table('tarea_cronogramas', function (Blueprint $table) {
            $table->string('enlace', 40)->nullable()->after('responsable');
        });
    }



    public function down(): void
    {
        Schema::table('tarea_cronogramas', function (Blueprint $table) {
            $table->dropColumn('enlace');
        });
    }
};
