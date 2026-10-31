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
        //  Cada abono es una fila. Los pagos se conservan aunque la
        // persona no asista: tesorería los sigue viendo en su lista.
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor', 10, 2);
            $table->date('fecha');
            $table->string('medio',60);
            $table->string('comprobante',255)->nullable();
            $table->string('nota',255)->nullable();

            $table->foreignId('inscripcion_id')->constrained('inscripcions')->restrictOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
