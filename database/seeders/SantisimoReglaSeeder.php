<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\SantisimoRegla;
use Illuminate\Database\Seeder;

class SantisimoReglaSeeder extends Seeder
{

    //  Las horas fijas que ya se conocen: Previstos cubre la madrugada y la
    // Junta Diocesana de 5:00 a 5:30, el sábado y el domingo.
    private const FIJAS = [
        ['Previstos', 1, '00:00:00', '04:00:00'],
        ['Previstos', 2, '00:00:00', '04:00:00'],
        ['Junta Diocesana', 1, '05:00:00', '05:30:00'],
        ['Junta Diocesana', 2, '05:00:00', '05:30:00'],
    ];

    //  En la rotación de septiembre estos equipos no tenían franjas de
    // adoración: solo cubren sus horas fijas (si las tienen).
    private const NO_PARTICIPAN = ['Directores', 'Tesorería', 'Premontaje', 'Junta Diocesana'];



    public function run(): void
    {
        // Ya se configuró desde la aplicación: no se toca
        if (SantisimoRegla::exists()) {
            return;
        }

        foreach (self::FIJAS as [$nombre, $dia, $desde, $hasta]) {
            $equipo = Equipo::where('nombre', $nombre)->first();

            if (! $equipo) {
                continue;
            }

            SantisimoRegla::firstOrCreate([
                'tipo'         => SantisimoRegla::FIJA,
                'dia_relativo' => $dia,
                'hora_desde'   => $desde,
                'hora_hasta'   => $hasta,
                'equipo_id'    => $equipo->id,
            ]);
        }

        Equipo::whereIn('nombre', self::NO_PARTICIPAN)->update(['participa_santisimo' => false]);
    }
}
