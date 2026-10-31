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
        //  Documentos, charlas y letreros ya hechos. El archivo se guarda en
        // una carpeta privada; aquí solo queda dónde está y cómo se llama.
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->string('categoria',20);
            $table->string('titulo',150);
            $table->string('descripcion',255)->nullable();
            $table->unsignedSmallInteger('numero')->nullable();
            $table->string('ruta',255);
            $table->string('nombre_original',255);
            $table->string('tipo_mime',120);
            $table->unsignedInteger('tamano');

            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->timestamps();

            $table->index(['categoria', 'numero']);

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
