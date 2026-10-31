<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\SantisimoBase;
use Illuminate\Database\Seeder;

class SantisimoBaseSeeder extends Seeder
{

    //  Borrador tomado de la rotación del retiro 89 M - 90 H (septiembre).
    // Allá había 3 equipos por franja; aquí va UNO: de los que estaban, el
    // que lleve menos franjas hasta ese momento. Las franjas que allá no
    // existían quedan vacías para llenarlas a mano.
    private const ROTACION = [
        '0-20:00' => ['Montaje', 'Floristería', 'Cafetería'],
        '0-20:30' => ['Coro', 'Librería', 'Montaje'],
        '0-21:00' => ['Cocina', 'Librería', 'Previstos'],
        '0-21:30' => ['Coro', 'Cocina', 'Floristería'],
        '0-22:00' => 'Horas',
        '0-22:30' => 'Horas',
        '0-23:00' => ['Floristería', 'Librería', 'Montaje'],
        '0-23:30' => ['Previstos', 'Montaje', 'Cafetería'],
        '1-04:00' => ['Montaje', 'Coro', 'Floristería'],
        '1-04:30' => ['Librería', 'Coro', 'Cafetería'],
        '1-05:00' => ['Junta Diocesana'],
        '1-05:30' => ['Secretaría', 'Montaje', 'Cafetería'],
        '1-06:00' => 'Laudes',
        '1-06:30' => 'Laudes',
        '1-07:00' => 'Eucaristía',
        '1-07:30' => 'Eucaristía',
        '1-08:00' => ['Cocina', 'Librería', 'Cafetería'],
        '1-08:30' => ['Coro', 'Librería', 'Montaje'],
        '1-09:00' => ['Secretaría', 'Librería', 'Montaje'],
        '1-09:30' => ['Montaje', 'Cocina', 'Secretaría'],
        '1-10:00' => ['Montaje', 'Cafetería', 'Secretaría'],
        '1-10:30' => ['Montaje', 'Floristería', 'Cafetería'],
        '1-11:00' => ['Cocina', 'Floristería', 'Cafetería'],
        '1-11:30' => ['Cocina', 'Floristería', 'Cafetería'],
        '1-12:00' => ['Cocina', 'Librería', 'Floristería'],
        '1-12:30' => ['Secretaría', 'Cocina', 'Previstos'],
        '1-13:00' => ['Secretaría', 'Cafetería', 'Previstos'],
        '1-13:30' => ['Coro', 'Cocina', 'Previstos'],
        '1-14:00' => ['Montaje', 'Previstos', 'Librería'],
        '1-14:30' => ['Montaje', 'Previstos', 'Coro'],
        '1-15:00' => 'Nonas',
        '1-15:30' => 'Nonas',
        '1-16:00' => ['Montaje', 'Previstos', 'Coro'],
        '1-16:30' => ['Montaje', 'Previstos', 'Librería'],
        '1-17:00' => ['Cafetería', 'Secretaría', 'Librería'],
        '1-17:30' => ['Cocina', 'Previstos', 'Floristería'],
        '1-18:00' => ['Montaje', 'Cocina', 'Cafetería'],
        '1-18:30' => ['Montaje', 'Floristería', 'Cafetería'],
        '1-19:00' => ['Coro', 'Secretaría', 'Montaje'],
        '1-19:30' => ['Cocina', 'Librería', 'Previstos'],
        '1-20:00' => ['Coro', 'Cocina', 'Floristería'],
        '1-20:30' => ['Montaje', 'Cocina', 'Coro'],
        '1-21:30' => ['Floristería', 'Cafetería', 'Montaje'],
        '1-22:00' => ['Previstos', 'Montaje', 'Cafetería'],
        '1-22:30' => ['Secretaría', 'Librería', 'Montaje'],
        '1-23:00' => ['Secretaría', 'Librería', 'Montaje'],
        '1-23:30' => ['Coro', 'Cocina', 'Librería'],
        '2-04:00' => 'Rosario',
        '2-04:30' => 'Rosario',
        '2-05:00' => ['Junta Diocesana'],
        '2-05:30' => ['Cafetería', 'Cocina', 'Montaje'],
        '2-06:00' => ['Coro', 'Librería', 'Montaje'],
        '2-06:30' => ['Montaje', 'Cocina', 'Secretaría'],
        '2-07:00' => ['Cafetería', 'Cocina', 'Montaje'],
        '2-07:30' => ['Coro', 'Previstos', 'Floristería'],
        '2-08:00' => ['Coro', 'Librería', 'Montaje'],
        '2-08:30' => ['Secretaría', 'Librería', 'Montaje'],
        '2-09:30' => ['Montaje', 'Cocina', 'Secretaría'],
        '2-10:00' => ['Montaje', 'Cafetería', 'Secretaría'],
        '2-10:30' => ['Montaje', 'Floristería', 'Cafetería'],
        '2-11:00' => ['Cocina', 'Librería', 'Cafetería'],
        '2-11:30' => ['Montaje', 'Floristería', 'Cafetería'],
        '2-12:00' => ['Coro', 'Previstos', 'Floristería'],
        '2-12:30' => ['Coro', 'Librería', 'Montaje'],
    ];



    public function run(): void
    {
        //  La base la edita la gente desde la aplicación: si ya existe no se
        // toca, para no devolver cambios ni revivir franjas quitadas.
        if (SantisimoBase::exists()) {
            return;
        }

        $equipos = Equipo::pluck('id', 'nombre');
        $usadas  = [];

        // Del viernes 5:30 p. m. al domingo 4:00 p. m.: 93 franjas
        $inicio = now()->startOfDay()->setTime(17, 30);
        $fin    = now()->startOfDay()->addDays(2)->setTime(16, 0);

        for ($momento = $inicio->copy(); $momento->lt($fin); $momento->addMinutes(30)) {
            $dia   = (int) $inicio->copy()->startOfDay()->diffInDays($momento->copy()->startOfDay());
            $hora  = $momento->format('H:i');
            $datos = ['todos' => false, 'actividad' => null, 'equipo_id' => null];

            // La madrugada es siempre de Previstos (fija)
            if ($dia > 0 && $hora < '04:00') {
                $datos['equipo_id'] = $equipos['Previstos'] ?? null;
            }

            $rotacion = self::ROTACION[$dia . '-' . $hora] ?? null;

            if (is_string($rotacion)) {
                $datos['todos']     = true;
                $datos['actividad'] = $rotacion;
            }

            if (is_array($rotacion)) {
                $elegido = collect($rotacion)
                    ->filter(fn ($nombre) => isset($equipos[$nombre]))
                    ->sortBy(fn ($nombre, $posicion) => [($usadas[$nombre] ?? 0), $posicion])
                    ->first();

                if ($elegido) {
                    $usadas[$elegido]   = ($usadas[$elegido] ?? 0) + 1;
                    $datos['equipo_id'] = $equipos[$elegido];
                }
            }

            SantisimoBase::firstOrCreate(['dia_relativo' => $dia, 'hora_inicio' => $hora . ':00'], $datos);
        }
    }
}
