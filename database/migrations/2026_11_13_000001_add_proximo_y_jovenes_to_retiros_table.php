<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  es_proximo: el retiro que se anuncia en las cartas al presbítero y
    // que abre sus inscripciones cuando se cierran las del actual (solo
    // uno a la vez). es_jovenes: el retiro de jóvenes, uno al año.
    public function up(): void
    {
        Schema::table('retiros', function (Blueprint $table) {
            $table->boolean('es_proximo')->default(false)->after('inscripciones_abiertas');
            $table->boolean('es_jovenes')->default(false)->after('es_proximo');
        });
    }



    public function down(): void
    {
        Schema::table('retiros', function (Blueprint $table) {
            $table->dropColumn(['es_proximo', 'es_jovenes']);
        });
    }
};
