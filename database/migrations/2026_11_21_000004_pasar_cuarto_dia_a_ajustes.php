<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    //  El Cuarto día deja de ser una perseverancia: solo sale en el
    // directorio, con fecha, hora y lugar (Ajustes). Nunca en el carrusel
    // ni en el mapa del inicio. Se pasa lo que ya estaba escrito.
    public function up(): void
    {
        $cuarto = DB::table('perseverancias')->whereRaw('LOWER(tipo) = ?', ['cuarto día'])->orderBy('orden')->first();

        if (! $cuarto) {
            return;
        }

        $lugar = collect([$cuarto->parroquia, $cuarto->direccion])->filter()->implode(', ');
        $ahora = now();

        foreach (['cuarto_dia_fecha' => $cuarto->dia, 'cuarto_dia_hora' => $cuarto->hora, 'cuarto_dia_lugar' => $lugar] as $clave => $valor) {
            if (filled($valor) && ! DB::table('ajustes')->where('clave', $clave)->exists()) {
                DB::table('ajustes')->insert(['clave' => $clave, 'valor' => $valor, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        }

        DB::table('perseverancias')->whereRaw('LOWER(tipo) = ?', ['cuarto día'])->delete();
    }



    //  Vuelve a ser una perseverancia con lo que haya en Ajustes
    public function down(): void
    {
        $valores = DB::table('ajustes')->whereIn('clave', ['cuarto_dia_fecha', 'cuarto_dia_hora', 'cuarto_dia_lugar'])->pluck('valor', 'clave');

        if ($valores->isEmpty()) {
            return;
        }

        DB::table('perseverancias')->insert([
            'tipo'       => 'Cuarto día',
            'dia'        => $valores['cuarto_dia_fecha'] ?? '',
            'hora'       => $valores['cuarto_dia_hora'] ?? '',
            'parroquia'  => $valores['cuarto_dia_lugar'] ?? '',
            'en_inicio'  => false,
            'orden'      => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
