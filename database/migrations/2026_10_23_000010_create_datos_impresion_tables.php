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
        //  El rectorado cambia en cada retiro y es distinto para hombres
        // y mujeres. Va en el encabezado del directorio.
        Schema::create('rectorados', function (Blueprint $table) {
            $table->id();
            $table->string('sexo',10);
            $table->string('cargo',40);
            $table->string('nombre',100);
            $table->string('ciudad',60)->nullable();
            $table->string('telefono',20)->nullable();
            $table->unsignedTinyInteger('orden')->default(0);

            $table->foreignId('retiro_id')->constrained('retiros')->cascadeOnDelete();

            $table->timestamps();

        });

        // Quienes firman la carta al presbítero (no cambian con cada retiro)
        Schema::create('firmantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            $table->string('cargo',100);
            $table->string('celular',20)->nullable();
            $table->unsignedTinyInteger('orden')->default(0);

            $table->timestamps();

        });

        // Parroquias donde perseverar; van al pie del directorio
        Schema::create('perseverancias', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',40)->default('Perseverancia');
            $table->string('dia',30);
            $table->string('hora',20);
            $table->string('parroquia',120);
            $table->string('direccion',120)->nullable();
            $table->unsignedTinyInteger('orden')->default(0);

            $table->timestamps();

        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perseverancias');
        Schema::dropIfExists('firmantes');
        Schema::dropIfExists('rectorados');
    }
};
