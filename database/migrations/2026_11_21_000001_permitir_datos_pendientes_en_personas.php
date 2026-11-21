<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  Quien llega sin haberse inscrito se agrega con lo poco que se sabe
    // (Secretaría · Agregar persona): la cédula, la fecha de nacimiento y
    // la talla pueden quedar pendientes y se completan después, en la
    // verificación de datos. La inscripción pública los sigue pidiendo.
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable()->change();
            $table->date('fecha_nacimiento')->nullable()->change();
            $table->string('talla_camiseta', 5)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable(false)->change();
            $table->date('fecha_nacimiento')->nullable(false)->change();
            $table->string('talla_camiseta', 5)->nullable(false)->change();
        });
    }
};
