<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Retiro;

class LetreroController extends Controller
{

    //  Letreros para pegar en la casa del retiro, hechos aquí para verlos
    // antes de imprimir. Reemplazan los Word y PowerPoint de la carpeta
    // "Letreros". clave => [nombre, descripción, cuándo se usa].
    public const LETREROS = [
        'silencio'          => ['Silencio, estamos en oración', 'Para la capilla y los pasillos cerca de las salas.', 'Viernes · al arreglar la casa'],
        'santisimo'         => ['Adoración al Santísimo', 'Para la puerta de la capilla del Santísimo.', 'Viernes · al arreglar la casa'],
        'ayudasDeDios'      => ['Ayudas de Dios', 'Para las canastas: horario de recepción y qué cartas no se reciben. Trae el número del retiro.', 'Viernes · en las canastas'],
        'nequi'             => ['Pago por Nequi', 'Una hoja para Librería, Cafetería y Floristería. El número y el titular se cambian en Ajustes.', 'Viernes · en Librería, Cafetería y Floristería'],
        'noCepillarse'      => ['No cepillarse aquí', 'Para los lavamanos donde no se deben cepillar los dientes.', 'Viernes · al arreglar los baños'],
        'residuos'          => ['Residuos de comida', 'Para la caneca de los restos de comida.', 'Viernes · al arreglar el comedor'],
        'basura'            => ['Basura', 'Para las canecas de papeles, plásticos y empaques.', 'Viernes · al arreglar la casa'],
        'noPapeles'         => ['No arrojar papeles al inodoro', 'Para cada baño.', 'Viernes · al arreglar los baños'],
        'banoFueraServicio' => ['Baño fuera de servicio', 'Para la puerta del baño que no se puede usar.', 'Cuando un baño se daña'],
        'soloGrupo'         => ['Solo hombres / Solo mujeres', 'Dos hojas, sin número de mesa: para marcar baños, cuartos o zonas de cada grupo.', 'Viernes · al arreglar la casa'],
        'bebidas'           => ['Bebidas para hombres / para mujeres', 'Dos hojas: una para la mesa de bebidas de los hombres y otra para la de las mujeres.', 'Viernes · en las mesas de bebidas'],
        'abreLaCaja'        => ['Abre la caja', 'Cinco hojas: el título, los pasos 1, 2 y 3, y «Lee por favor».', 'Domingo · para la dinámica de la caja'],
        'etiquetasMesa'     => ['Etiquetas de mesa', 'Una hoja de etiquetas pequeñas por mesa, para marcar sobres, cajas y paquetes.', 'Antes del retiro · para sobres y cajas'],
    ];

    // Los equipos que reciben pagos y llevan su letrero de Nequi
    private const EQUIPOS_NEQUI = ['Librería', 'Cafetería', 'Floristería'];



    // La galería: cada letrero con su vista previa
    public function index()
    {
        return view('letreros.index', [
            'retiro'   => Retiro::actual(),
            'letreros' => self::LETREROS,
        ]);
    }



    public function show(string $clave)
    {
        abort_unless(array_key_exists($clave, self::LETREROS), 404);

        $retiro = Retiro::actual();

        return view('letreros.hoja', [
            'clave'       => $clave,
            'retiro'      => $retiro,
            'cuenta'      => self::cuentaNequi(),
            'equipos'     => self::EQUIPOS_NEQUI,
            'mesas'       => $this->mesasPorGrupo($retiro),
            'titulo'      => self::LETREROS[$clave][0],
            'estilo'      => 'css/letrerosHoja.css',
            'volver'      => route('letreros.index'),
            'volverTexto' => 'Volver a letreros',
        ]);
    }



    //  La cuenta del letrero de Nequi: la de Ajustes y, si allá está
    // vacía, la cuenta Nequi del retiro actual. Null si no hay ninguna.
    public static function cuentaNequi(): ?object
    {
        $delRetiro = Retiro::actual()?->cuentasPago->first(fn ($cuenta) => stripos($cuenta->entidad, 'nequi') !== false);

        $numero  = Ajuste::valor(Ajuste::NEQUI_NUMERO) ?: $delRetiro?->numero;
        $titular = Ajuste::valor(Ajuste::NEQUI_TITULAR) ?: $delRetiro?->titular;

        return $numero ? (object) ['numero' => $numero, 'titular' => $titular] : null;
    }



    //  Para las etiquetas: las mesas creadas de cada grupo o, si todavía
    // no hay, de la 1 a la 10.
    private function mesasPorGrupo(?Retiro $retiro): array
    {
        $grupos = [];

        foreach (['Masculino', 'Femenino'] as $sexo) {
            $numeros = $retiro ? $retiro->mesas()->where('sexo', $sexo)->orderBy('numero')->pluck('numero')->all() : [];

            $grupos[$sexo] = $numeros ?: range(1, 10);
        }

        return $grupos;
    }
}
