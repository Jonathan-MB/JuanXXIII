<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  Un PDF para ver en la página los Word, Excel y PowerPoint sin
    // descargarlos (de un Excel, con todas sus hojas). El original sigue
    // en "ruta" para descargarlo y editarlo.
    public function up(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            $table->string('vista_previa', 255)->nullable()->after('ruta');
        });
    }



    public function down(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            $table->dropColumn('vista_previa');
        });
    }
};
