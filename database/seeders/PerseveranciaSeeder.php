<?php

namespace Database\Seeders;

use App\Models\Perseverancia;
use Illuminate\Database\Seeder;

class PerseveranciaSeeder extends Seeder
{
    //  Las del directorio de junio de 2026, con los días y horarios de los
    // afiches de noviembre de 2026. Se cambian desde Perseverancias.
    public const PERSEVERANCIAS = [
        ['tipo' => 'Cuarto día',        'sector' => 'Alfonso Bonilla Aragón', 'dia' => 'Jueves',    'hora' => '6:30 p. m.', 'parroquia' => 'Nuestra Señora de la Candelaria',                 'direccion' => 'Calle 81 # 27B-45, B/ Alfonso Bonilla Aragón', 'mapa' => null, 'actividades' => null],
        ['tipo' => 'Escuela diocesana', 'sector' => 'Virtual',                'dia' => 'Sábado',    'hora' => '7:00 p. m.', 'parroquia' => 'Virtual',                                         'direccion' => null, 'mapa' => null, 'actividades' => null],
        ['tipo' => 'Perseverancia',     'sector' => 'El Rodeo',               'dia' => 'Martes',    'hora' => '7:00 p. m.', 'parroquia' => 'Parroquia La Natividad del Señor',                'direccion' => 'Cra 25A # 39-36, B/ El Rodeo', 'mapa' => null, 'actividades' => "7:00 p. m. Sagrada Eucaristía\n7:40 p. m. Perseverancia"],
        ['tipo' => 'Perseverancia',     'sector' => 'Gaitán',                 'dia' => 'Martes',    'hora' => '6:00 p. m.', 'parroquia' => 'Parroquia Nuestra Señora de Nazaret',             'direccion' => 'Cl 71A # 1H-106, B/ Gaitán', 'mapa' => null, 'actividades' => null],
        ['tipo' => 'Perseverancia',     'sector' => 'Jamundí',                'dia' => 'Martes',    'hora' => '6:30 p. m.', 'parroquia' => 'Parroquia Cristo Sacerdote',                      'direccion' => 'Calle 18 # 8-33, Jamundí', 'mapa' => 'Parroquia Cristo Sacerdote, Calle 18 # 8-33, Jamundí, Valle del Cauca', 'actividades' => '6:30 p. m. Perseverancia'],
        ['tipo' => 'Perseverancia',     'sector' => 'Ciudad 2000',            'dia' => 'Miércoles', 'hora' => '7:30 p. m.', 'parroquia' => 'Parroquia Jesús Misericordioso',                  'direccion' => 'Cl 33B # 67A-55, B/ Ciudad 2000', 'mapa' => null, 'actividades' => '7:30 p. m. Perseverancia'],
        ['tipo' => 'Perseverancia',     'sector' => 'El Pondaje',             'dia' => 'Jueves',    'hora' => '6:30 p. m.', 'parroquia' => 'Santuario Señor Jesús de la Divina Misericordia', 'direccion' => 'Cra 28 # 72-04, El Pondaje', 'mapa' => null, 'actividades' => "6:30 p. m. Rosario\n7:00 p. m. Sagrada Eucaristía\n8:00 p. m. Perseverancia"],
        ['tipo' => 'Perseverancia',     'sector' => 'San Nicolás',            'dia' => 'Jueves',    'hora' => '5:30 p. m.', 'parroquia' => 'Parroquia San Nicolás de Bari',                   'direccion' => 'Cl 20 # 5-33, B/ San Nicolás', 'mapa' => null, 'actividades' => "5:30 p. m. Adoración al Santísimo\n6:30 p. m. Sagrada Eucaristía\n7:30 p. m. Perseverancia"],
        ['tipo' => 'Perseverancia',     'sector' => 'Siloé',                  'dia' => 'Viernes',   'hora' => '6:30 p. m.', 'parroquia' => 'Parroquia San Matías Apóstol',                    'direccion' => 'Dg 51 Oeste # 10-96, B/ Venezuela, Siloé', 'mapa' => null, 'actividades' => "6:30 p. m. Rosario\n7:00 p. m. Sagrada Eucaristía\n8:00 p. m. Perseverancia"],
        ['tipo' => 'Perseverancia',     'sector' => 'Meléndez',               'dia' => 'Viernes',   'hora' => '7:00 p. m.', 'parroquia' => 'Parroquia Santa María Reina',                     'direccion' => 'Cra 94 # 4B-00, B/ Meléndez', 'mapa' => null, 'actividades' => "7:00 p. m. Sagrada Eucaristía\nPerseverancia"],
        ['tipo' => 'Perseverancia',     'sector' => 'Floralia',               'dia' => 'Viernes',   'hora' => '7:00 p. m.', 'parroquia' => 'Parroquia María Misionera',                       'direccion' => 'Cl 72J # 5N-81, B/ Floralia', 'mapa' => null, 'actividades' => "7:00 p. m. Sagrada Eucaristía\nPerseverancia"],
    ];



    //  El punto de cada una en el mapa, sacado de OpenStreetMap (la
    // iglesia o la esquina de su dirección). Alfonso Bonilla Aragón y El
    // Pondaje son aproximadas: se corrigen desde Perseverancias.
    public const COORDENADAS = [
        'Alfonso Bonilla Aragón' => [3.421107, -76.482717],
        'El Rodeo'               => [3.435584, -76.50397],
        'Gaitán'                 => [3.475206, -76.485192],
        'Jamundí'                => [3.266826, -76.53583],
        'Ciudad 2000'            => [3.397839, -76.520322],
        'El Pondaje'             => [3.428871, -76.498204],
        'San Nicolás'            => [3.454649, -76.525206],
        'Siloé'                  => [3.414822, -76.560946],
        'Meléndez'               => [3.37482, -76.546168],
        'Floralia'               => [3.495673, -76.496109],
    ];



    //  La imagen de fondo de cada una (en database/seeders/imagenes) y su
    // crédito. Son de Wikimedia Commons: obras de dominio público y la
    // todas son obras de dominio público.
    public const IMAGENES = [
        'Alfonso Bonilla Aragón' => ['alfonso-bonilla-aragon.jpg', 'Fra Angelico · Public domain · Wikimedia Commons'],
        'Virtual'                => ['virtual.jpg', 'Caravaggio · Public domain · Wikimedia Commons'],
        'El Rodeo'               => ['el-rodeo.jpg', 'Gerard van Honthorst · Public domain · Wikimedia Commons'],
        'Gaitán'                 => ['gaitan.jpg', 'Bartolomé Esteban Murillo · Public domain · Wikimedia Commons'],
        'Jamundí'                => ['jamundi.jpg', 'Juan de Juanes · Public domain · Wikimedia Commons'],
        'Ciudad 2000'            => ['ciudad-2000.jpg', 'Rembrandt · Public domain · Wikimedia Commons'],
        'El Pondaje'             => ['el-pondaje.jpg', 'Eugeniusz Kazimirowski · Public domain · Wikimedia Commons'],
        'San Nicolás'            => ['san-nicolas.jpg', 'Carlo Crivelli · Public domain · Wikimedia Commons'],
        'Siloé'                  => ['siloe.jpg', 'Peter Paul Rubens · Public domain · Wikimedia Commons'],
        'Meléndez'               => ['melendez.jpg', 'Diego Velázquez · Public domain · Wikimedia Commons'],
        'Floralia'               => ['floralia.jpg', 'Bartolomé Esteban Murillo · Public domain · Wikimedia Commons'],
    ];



    public function run(): void
    {
        //  Se editan desde la app: si ya hay perseverancias no se toca
        // nada; si no, una editada volvería a aparecer como estaba antes.
        if (Perseverancia::exists()) {
            return;
        }

        foreach (self::PERSEVERANCIAS as $orden => $perseverancia) {
            [$latitud, $longitud] = self::COORDENADAS[$perseverancia['sector']] ?? [null, null];
            [$archivo, $credito]  = self::IMAGENES[$perseverancia['sector']] ?? [null, null];

            // La imagen se copia a la carpeta privada, como si se hubiera subido
            $imagen = null;
            if ($archivo && is_file(__DIR__ . '/imagenes/perseverancias/' . $archivo)) {
                $imagen = Perseverancia::CARPETA_IMAGENES . '/' . $archivo;
                Storage::put($imagen, file_get_contents(__DIR__ . '/imagenes/perseverancias/' . $archivo));
            }

            Perseverancia::create($perseverancia + [
                'orden'          => $orden + 1,
                'en_inicio'      => $perseverancia['direccion'] !== null,
                'latitud'        => $latitud,
                'longitud'       => $longitud,
                'imagen'         => $imagen,
                'imagen_credito' => $imagen ? $credito : null,
            ]);
        }
    }
}
