<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  La hoja de cumpleaños también saluda a quienes sirven en el retiro:
    // el rectorado (Datos para imprimir) y los catequistas de cada mesa (Mesas).
    public function up(): void
    {
        Schema::table('rectorados', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('telefono');
        });

        Schema::table('mesas', function (Blueprint $table) {
            $table->date('titular_nacimiento')->nullable()->after('titular_barrio');
            $table->date('auxiliar_nacimiento')->nullable()->after('auxiliar_barrio');
        });
    }



    public function down(): void
    {
        Schema::table('rectorados', function (Blueprint $table) {
            $table->dropColumn('fecha_nacimiento');
        });

        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn(['titular_nacimiento', 'auxiliar_nacimiento']);
        });
    }
};
