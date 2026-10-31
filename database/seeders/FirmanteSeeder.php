<?php

namespace Database\Seeders;

use App\Models\Firmante;
use Illuminate\Database\Seeder;

class FirmanteSeeder extends Seeder
{
    public function run(): void
    {
        //  Se editan desde la app: si ya hay firmantes no se toca nada,
        // para no revivir uno que se quitó al actualizar el servidor.
        if (Firmante::exists()) {
            return;
        }

        //  Los firmantes que trae la carta en Word del movimiento.
        // Se cambian desde Impresiones → Datos para imprimir.
        $firmantes = [
            ['nombre' => 'Pbro. Alfaro Puentes Mesa',  'cargo' => 'Consejero espiritual Juan XXIII',     'celular' => '311 373 8695', 'orden' => 1],
            ['nombre' => 'Cristian Angulo Gutiérrez',  'cargo' => 'Presidente jurisdiccional Juan XXIII', 'celular' => '301 404 7574', 'orden' => 2],
        ];

        foreach ($firmantes as $firmante) {
            Firmante::firstOrCreate(['orden' => $firmante['orden']], $firmante);
        }
    }
}
