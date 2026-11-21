<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //  El nombre (santo) de cada mesa según su número, por grupo. Casi no
    // cambia de un retiro a otro: al crear una mesa sin nombre, toma este.
    public function up(): void
    {
        Schema::create('nombres_mesa', function (Blueprint $table) {
            $table->id();
            $table->string('sexo', 10);
            $table->unsignedTinyInteger('numero');
            $table->string('nombre', 60);
            $table->timestamps();

            $table->unique(['sexo', 'numero']);
        });

        //  Arranca con los nombres que ya tienen las mesas: si un número tuvo
        // varios nombres, el del retiro más reciente
        $mesas = DB::table('mesas')
            ->whereNotNull('nombre')
            ->where('nombre', '!=', '')
            ->orderByDesc('retiro_id')
            ->get(['sexo', 'numero', 'nombre']);

        $ahora = now();

        foreach ($mesas->unique(fn ($mesa) => $mesa->sexo . '-' . $mesa->numero) as $mesa) {
            DB::table('nombres_mesa')->insert([
                'sexo'       => $mesa->sexo,
                'numero'     => $mesa->numero,
                'nombre'     => trim($mesa->nombre),
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }



    public function down(): void
    {
        Schema::dropIfExists('nombres_mesa');
    }
};
