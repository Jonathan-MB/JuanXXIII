<?php

namespace Database\Seeders;

use App\Models\Equipo;
use Illuminate\Database\Seeder;

class EquipoSeeder extends Seeder
{
    public function run(): void
    {
        //  Los equipos que se conocen hoy. La lista se puede ampliar
        // desde la aplicación; el seeder solo agrega los que falten.
        $equipos = [
            'Directores',
            'Previstos',
            'Secretaría',
            'Librería',
            'Floristería',
            'Cafetería',
            'Cocina',
            'Montaje',
            'Tesorería',
            'Coro',
            'Premontaje',
            'Junta Diocesana',
        ];

        foreach ($equipos as $nombre) {
            Equipo::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
