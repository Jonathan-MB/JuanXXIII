<?php

namespace Database\Seeders;

use App\Models\Perseverancia;
use Illuminate\Database\Seeder;

class PerseveranciaSeeder extends Seeder
{
    public function run(): void
    {
        //  Se editan desde la app: si ya hay perseverancias no se toca
        // nada; si no, una editada volvería a aparecer como estaba antes.
        if (Perseverancia::exists()) {
            return;
        }

        //  Las perseverancias del directorio del retiro de junio de 2026.
        // Se cambian desde Impresiones → Datos para imprimir.
        $perseverancias = [
            ['tipo' => 'Cuarto día',        'dia' => 'Jueves',    'hora' => '6:30 p. m.', 'parroquia' => 'Nuestra Señora de la Candelaria',                'direccion' => 'Calle 81 # 27B-45, B/ Alfonso Bonilla Aragón'],
            ['tipo' => 'Escuela diocesana', 'dia' => 'Sábado',    'hora' => '7:00 p. m.', 'parroquia' => 'Virtual',                                        'direccion' => null],
            ['tipo' => 'Perseverancia',     'dia' => 'Martes',    'hora' => '7:00 p. m.', 'parroquia' => 'Parroquia La Natividad del Señor',               'direccion' => 'Cra 25A # 39-36, B/ El Rodeo'],
            ['tipo' => 'Perseverancia',     'dia' => 'Martes',    'hora' => '6:00 p. m.', 'parroquia' => 'Parroquia Nuestra Señora de Nazaret',            'direccion' => 'Cl 71A # 1H-106, B/ Gaitán'],
            ['tipo' => 'Perseverancia',     'dia' => 'Miércoles', 'hora' => '7:00 p. m.', 'parroquia' => 'Parroquia Jesús Misericordioso',                 'direccion' => 'Cl 33B # 67A-55, B/ Ciudad 2000'],
            ['tipo' => 'Perseverancia',     'dia' => 'Jueves',    'hora' => '6:30 p. m.', 'parroquia' => 'Parroquia Cristo Sacerdote',                     'direccion' => 'Calle 18 # 8-33, Jamundí'],
            ['tipo' => 'Perseverancia',     'dia' => 'Jueves',    'hora' => '6:30 p. m.', 'parroquia' => 'Santuario Señor Jesús de la Divina Misericordia', 'direccion' => 'Cra 28 # 72-04, El Pondaje'],
            ['tipo' => 'Perseverancia',     'dia' => 'Jueves',    'hora' => '5:30 p. m.', 'parroquia' => 'Parroquia San Nicolás de Bari',                  'direccion' => 'Cl 20 # 5-33, B/ San Nicolás'],
            ['tipo' => 'Perseverancia',     'dia' => 'Viernes',   'hora' => '6:30 p. m.', 'parroquia' => 'Parroquia San Matías Apóstol',                   'direccion' => 'Dg 51 Oeste # 10-96, B/ Siloé'],
            ['tipo' => 'Perseverancia',     'dia' => 'Viernes',   'hora' => '7:00 p. m.', 'parroquia' => 'Parroquia Santa María Reina',                    'direccion' => 'Cra 94 # 4B-00, B/ Meléndez'],
            ['tipo' => 'Perseverancia',     'dia' => 'Viernes',   'hora' => '6:30 p. m.', 'parroquia' => 'Parroquia María Misionera',                      'direccion' => 'Cl 72J # 5N-81, B/ Floralia'],
        ];

        foreach ($perseverancias as $orden => $perseverancia) {
            Perseverancia::firstOrCreate(
                ['parroquia' => $perseverancia['parroquia'], 'dia' => $perseverancia['dia']],
                $perseverancia + ['orden' => $orden + 1]
            );
        }
    }
}
