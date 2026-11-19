<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\Equipo;
use App\Models\Inscripcion;
use App\Models\Mesa;
use App\Models\MovimientoInventario;
use App\Models\Persona;
use App\Models\Retiro;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

//  Datos inventados para probar la aplicación con volumen. No va en
// DatabaseSeeder: se corre a propósito con
//   php artisan db:seed --class=DatosPruebaSeeder
// Todo lo que crea se reconoce fácil: cédulas que empiezan por 990,
// correos @demo.test, retiros con «DATOS DE PRUEBA» en «qué incluye»,
// catequistas y notas de inventario con «(prueba)». Se puede correr dos
// veces sin duplicar nada, y se quita todo con
//   php artisan datos-prueba:quitar
class DatosPruebaSeeder extends Seeder
{

    private const OFRENDA = 210000;

    // Marcas para reconocer (y quitar) lo que crea este seeder
    public const MARCA_RETIRO = "DATOS DE PRUEBA";
    public const MARCA        = "(prueba)";

    private const NOMBRES_HOMBRE = ['Juan Carlos', 'Andrés', 'Jhon Jairo', 'Luis Fernando', 'Carlos Arturo', 'Diego', 'Jorge Iván', 'Óscar', 'William', 'Miguel Ángel', 'José Luis', 'Fabián', 'Hernán', 'Édgar', 'Jhonatan', 'Cristian', 'Sebastián', 'Mauricio', 'Wilson', 'Duván', 'Brayan', 'Juan David', 'Julián', 'Alexander', 'Ramiro', 'Gustavo', 'Héctor', 'Camilo', 'Yeison', 'Nicolás'];

    private const NOMBRES_MUJER = ['María Fernanda', 'Luz Dary', 'Paola Andrea', 'Diana Marcela', 'Gloria Inés', 'Sandra Milena', 'Yuliana', 'Claudia Patricia', 'Ángela María', 'Nubia', 'Leidy Johana', 'Carolina', 'Martha Lucía', 'Yolanda', 'Daniela', 'Valentina', 'Luz Marina', 'Adriana', 'Mónica', 'Érika', 'Natalia', 'Stella', 'Rosa Elena', 'Viviana', 'Lina María', 'Jessica', 'Beatriz', 'Liliana', 'Ana Milena', 'Karen'];

    private const APELLIDOS = ['Gómez', 'Rodríguez', 'Martínez', 'García', 'López', 'Hernández', 'González', 'Ramírez', 'Torres', 'Valencia', 'Mosquera', 'Rentería', 'Caicedo', 'Ortiz', 'Castaño', 'Quintero', 'Muñoz', 'Arboleda', 'Ospina', 'Cárdenas', 'Riascos', 'Sinisterra', 'Mejía', 'Zapata', 'Bolaños', 'Paz', 'Lasso', 'Angulo', 'Solarte', 'Obando', 'Palacios', 'Velasco'];

    private const BARRIOS = ['El Rodeo', 'Ciudad Córdoba', 'Floralia', 'Alfonso López', 'La Flora', 'Siloé', 'Meléndez', 'El Caney', 'Ciudad 2000', 'Mojica', 'El Poblado', 'San Nicolás', 'Los Andes', 'El Pondaje', 'Manzanares', 'Calima', 'Potrero Grande', 'Villa del Lago', 'Belisario Caicedo', 'Prados del Sur'];

    private const CIUDADES = ['Cali', 'Cali', 'Cali', 'Cali', 'Cali', 'Jamundí', 'Palmira', 'Yumbo', 'Candelaria', 'Puerto Tejada'];

    private const EPS = ['Sura', 'Sanitas', 'Nueva EPS', 'Emssanar', 'Coosalud', 'Comfenalco', 'SOS', 'Salud Total'];

    private const PARENTESCOS = ['Esposa', 'Esposo', 'Mamá', 'Papá', 'Hermana', 'Hermano', 'Hija', 'Hijo', 'Tía', 'Amiga', 'Amigo', 'Prima'];

    private const MEDICAMENTOS = ['Losartán, 8 a. m.', 'Metformina después del almuerzo', 'Levotiroxina en ayunas', 'Insulina antes de cada comida', 'Omeprazol en ayunas', 'Atorvastatina, 9 p. m.'];

    private const TALLAS = ['S', 'M', 'M', 'L', 'L', 'L', 'XL', 'XL', 'XXL'];



    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('  DatosPruebaSeeder omitido: nunca se corre en producción.');
            return;
        }

        // Semilla fija: cada vez salen los mismos datos
        mt_srand(2026);

        $this->cuentasPorEquipo();

        [$pasado, $actual, $proximo] = $this->retiros();

        $this->inscritos($pasado, 'pasado', 30, 30);
        $this->inscritos($actual, 'actual', 70, 70);
        $this->rectoradoMujeres($actual);
        $this->inventarios();

        $this->command?->info('  Datos de prueba listos: cédulas 9901… (retiro pasado) y 9902… (retiro actual).');
    }



    //  Una cuenta demo por equipo (contraseña SEED_PASS_DEMO), más una de
    // Directivos, para probar lo que ve cada rol.
    private function cuentasPorEquipo(): void
    {
        $rolEquipo     = Rol::where('nombre', Rol::EQUIPO)->value('id');
        $rolDirectivos = Rol::where('nombre', Rol::DIRECTIVOS)->value('id');

        foreach (Equipo::all() as $equipo) {
            $usuario = Str::slug($equipo->nombre, '.');

            Usuario::firstOrCreate(['usuario' => $usuario], [
                'nombre'     => $equipo->nombre . ' Demo',
                'correo'     => $usuario . '@demo.test',
                'contrasena' => env('SEED_PASS_DEMO', 'demo12345'),
                'rol_id'     => $rolEquipo,
                'equipo_id'  => $equipo->id,
            ]);
        }

        Usuario::firstOrCreate(['usuario' => 'directivos'], [
            'nombre'     => 'Directivos Demo',
            'correo'     => 'directivos@demo.test',
            'contrasena' => env('SEED_PASS_DEMO', 'demo12345'),
            'rol_id'     => $rolDirectivos,
            'equipo_id'  => Equipo::where('nombre', 'Directores')->value('id'),
        ]);
    }



    //  Un retiro ya pasado (para el historial y el bloqueo de "ya vivió
    // el retiro"), el actual y el próximo (para la línea de la carta).
    private function retiros(): array
    {
        $base = [
            'ofrenda'            => self::OFRENDA,
            'que_incluye'        => self::MARCA_RETIRO,
            'porcentaje_reserva' => 50,
            'lugar'              => 'Colegio Nuestra Señora de Chiquinquirá',
            'direccion'          => 'Calle 7 oeste # 54-75, B. Belisario Caicedo',
            'ciudad'             => 'Santiago de Cali',
        ];

        //  El actual es el retiro real si ya existe; el pasado y el próximo
        // se numeran a partir de él para no chocar con retiros reales
        $actual = Retiro::actual() ?? Retiro::firstOrCreate(['numero_hombres' => 91], $base + [
            'numero_mujeres'         => 90,
            'fecha_inicio'           => '2026-11-20',
            'fecha_fin'              => '2026-11-22',
            'fecha_limite_pago'      => '2026-11-16',
            'inscripciones_abiertas' => true,
        ]);

        $pasado = Retiro::firstOrCreate(['numero_hombres' => $actual->numero_hombres - 1], $base + [
            'numero_mujeres'         => $actual->numero_mujeres - 1,
            'fecha_inicio'           => $actual->fecha_inicio->copy()->subMonths(2)->toDateString(),
            'fecha_fin'              => $actual->fecha_fin->copy()->subMonths(2)->toDateString(),
            'inscripciones_abiertas' => false,
        ]);

        $proximo = Retiro::firstOrCreate(['numero_hombres' => $actual->numero_hombres + 1], $base + [
            'numero_mujeres'         => $actual->numero_mujeres + 1,
            'fecha_inicio'           => '2027-02-19',
            'fecha_fin'              => '2027-02-21',
            'inscripciones_abiertas' => false,
        ]);

        foreach ([$pasado, $proximo] as $retiro) {
            if ($retiro->cuentasPago()->doesntExist()) {
                $retiro->cuentasPago()->create(['entidad' => 'Nequi', 'numero' => '315 572 4732', 'titular' => 'Gilma Chávez']);
            }
        }

        return [$pasado, $actual, $proximo];
    }



    //  Crea hombres y mujeres inscritos en un retiro, con estados,
    // pagos y puestos variados. En el pasado todos terminaron.
    private function inscritos(Retiro $retiro, string $etapa, int $hombres, int $mujeres): void
    {
        $prefijo = $etapa === 'pasado' ? 9901 : 9902;
        $numero  = 0;

        foreach (['Masculino' => $hombres, 'Femenino' => $mujeres] as $sexo => $cantidad) {
            $this->mesasDe($retiro, $sexo, (int) ceil($cantidad / 8) + 1);

            for ($i = 1; $i <= $cantidad; $i++) {
                $numero++;
                $cedula  = $prefijo . str_pad((string) $numero, 5, '0', STR_PAD_LEFT);
                $persona = $this->persona($cedula, $sexo, $numero);

                if (Inscripcion::where('persona_id', $persona->id)->where('retiro_id', $retiro->id)->exists()) {
                    continue;
                }

                $estado = $etapa === 'pasado' ? Inscripcion::TERMINO : $this->estadoAlAzar();

                // Tienen puesto: todos en el pasado; en el actual, los que llegaron y algunos confirmados
                $conPuesto = $etapa === 'pasado'
                    || $estado === Inscripcion::LLEGO
                    || ($estado === Inscripcion::CONFIRMADO && mt_rand(1, 100) <= 40);
                [$mesaId, $silla] = $conPuesto ? $this->sillaLibre($retiro, $sexo) : [null, null];

                $tienePadrino = mt_rand(1, 100) <= 45;

                $inscripcion = Inscripcion::create([
                    'estado'             => $estado,
                    'forma_pago'         => $this->elegir(['Nequi 315 572 4732 a nombre de Gilma Chávez', 'Nequi 310 388 6311 a nombre de Baldomero Rosero', 'Efectivo']),
                    'tiene_padrino'      => $tienePadrino,
                    'padrino_nombre'     => $tienePadrino ? $this->elegir(array_merge(self::NOMBRES_HOMBRE, self::NOMBRES_MUJER)) . ' ' . $this->elegir(self::APELLIDOS) : null,
                    'padrino_telefono'   => $tienePadrino ? $this->celular() : null,
                    'padrino_presente'   => $tienePadrino ? $this->elegir(['Sí, los tres días', 'Sí, en algunos momentos', 'No', null]) : null,
                    'acepta_datos'       => true,
                    'declara_requisitos' => true,
                    'silla'              => $silla,
                    'llegada_en'         => in_array($estado, [Inscripcion::LLEGO, Inscripcion::TERMINO], true) ? $retiro->fecha_inicio->copy()->setTime(17, mt_rand(0, 59)) : null,
                    'persona_id'         => $persona->id,
                    'retiro_id'          => $retiro->id,
                    'mesa_id'            => $mesaId,
                ]);

                foreach ([0, 1] as $c) {
                    $inscripcion->contactosEmergencia()->create([
                        'nombre'     => $this->elegir(array_merge(self::NOMBRES_HOMBRE, self::NOMBRES_MUJER)) . ' ' . $this->elegir(self::APELLIDOS),
                        'celular'    => $this->celular(),
                        'parentesco' => $this->elegir(self::PARENTESCOS),
                    ]);
                }

                $this->pagos($inscripcion, $etapa === 'pasado' ? 100 : mt_rand(1, 100));
            }
        }
    }



    private function persona(string $cedula, string $sexo, int $numero): Persona
    {
        $nombres = $sexo === 'Femenino' ? self::NOMBRES_MUJER : self::NOMBRES_HOMBRE;
        $toma    = mt_rand(1, 100) <= 25;

        return Persona::firstOrCreate(['cedula' => $cedula], [
            'nombres'          => $this->elegir($nombres),
            'apellidos'        => $this->elegir(self::APELLIDOS) . ' ' . $this->elegir(self::APELLIDOS),
            'sexo'             => $sexo,
            'fecha_nacimiento' => sprintf('%d-%02d-%02d', mt_rand(1950, 2008), mt_rand(1, 12), mt_rand(1, 28)),
            'correo'           => 'persona' . $cedula . '@demo.test',
            'celular'          => $this->celular(),
            'direccion'        => 'Calle ' . mt_rand(1, 120) . ' # ' . mt_rand(1, 99) . '-' . mt_rand(1, 99),
            'barrio'           => $this->elegir(self::BARRIOS),
            'ciudad'           => $this->elegir(self::CIUDADES),
            'eps'              => $this->elegir(self::EPS),
            'medicamentos'     => $toma ? $this->elegir(self::MEDICAMENTOS) : null,
            'talla_camiseta'   => $this->elegir(self::TALLAS),
            'bautismo'         => mt_rand(1, 100) <= 90,
            'primera_comunion' => mt_rand(1, 100) <= 70,
            'confirmacion'     => mt_rand(1, 100) <= 50,
            'matrimonio'       => mt_rand(1, 100) <= 35,
        ]);
    }



    // Reparto parecido al de un retiro real unos días antes y durante la llegada
    private function estadoAlAzar(): string
    {
        $dado = mt_rand(1, 100);

        return match (true) {
            $dado <= 10 => Inscripcion::NO_ASISTIO,
            $dado <= 15 => Inscripcion::ABANDONO,
            $dado <= 50 => Inscripcion::LLEGO,
            $dado <= 75 => Inscripcion::CONFIRMADO,
            default     => Inscripcion::INSCRITO,
        };
    }



    //  Pagó todo (40 %), abonó la mitad (35 %) o no ha pagado (25 %).
    // Solo crea pagos si la inscripción todavía no tiene.
    private function pagos(Inscripcion $inscripcion, int $dado): void
    {
        if ($inscripcion->pagos()->exists() || $dado > 75) {
            return;
        }

        $mitad = ($inscripcion->retiro->ofrenda ?: self::OFRENDA) / 2;
        $fecha = $inscripcion->retiro->fecha_inicio->copy()->subDays(mt_rand(5, 30));
        $medio = $this->elegir(['Nequi 315 572 4732', 'Nequi 310 388 6311', 'Efectivo']);

        $inscripcion->pagos()->create(['valor' => $mitad, 'fecha' => $fecha, 'medio' => $medio, 'nota' => 'Reserva']);

        if ($dado <= 40) {
            $inscripcion->pagos()->create(['valor' => $mitad, 'fecha' => $fecha->copy()->addDays(mt_rand(1, 4)), 'medio' => $medio, 'nota' => 'Saldo']);
        }
    }



    // Mesas de 8 sillas con su CT y CA, sin tocar las que ya existan
    private function mesasDe(Retiro $retiro, string $sexo, int $cantidad): void
    {
        $nombres = $sexo === 'Femenino' ? self::NOMBRES_MUJER : self::NOMBRES_HOMBRE;

        for ($numero = 1; $numero <= $cantidad; $numero++) {
            Mesa::firstOrCreate(
                ['retiro_id' => $retiro->id, 'sexo' => $sexo, 'numero' => $numero],
                [
                    'cantidad_sillas'     => 8,
                    'catequista_titular'  => $this->elegir($nombres) . ' ' . $this->elegir(self::APELLIDOS) . ' ' . self::MARCA,
                    'catequista_auxiliar' => $this->elegir($nombres) . ' ' . $this->elegir(self::APELLIDOS) . ' ' . self::MARCA,
                ]
            );
        }
    }



    //  La primera silla libre de las mesas del grupo, saltando las que ya
    // están ocupadas (también por los datos que se cargaron a mano).
    private function sillaLibre(Retiro $retiro, string $sexo): array
    {
        $mesas = Mesa::where('retiro_id', $retiro->id)->where('sexo', $sexo)->orderBy('numero')->get();

        foreach ($mesas as $mesa) {
            $ocupadas = Inscripcion::where('mesa_id', $mesa->id)->pluck('silla')->map(fn ($silla) => (int) $silla)->all();

            for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++) {
                if (! in_array($silla, $ocupadas, true)) {
                    return [$mesa->id, $silla];
                }
            }
        }

        // Todas llenas: se abre una mesa más
        $nueva = Mesa::create(['retiro_id' => $retiro->id, 'sexo' => $sexo, 'numero' => ($mesas->max('numero') ?? 0) + 1, 'cantidad_sillas' => 8, 'catequista_titular' => self::MARCA]);

        return [$nueva->id, 1];
    }



    private function rectoradoMujeres(Retiro $retiro): void
    {
        if ($retiro->rectorados()->where('sexo', 'Femenino')->exists()) {
            return;
        }

        $cargos = [
            ['Rector(a)', 'Luz Dary Caicedo'],
            ['Vicerrector(a)', 'Martha Lucía Ortiz'],
            ['Dirigente responsable', 'Gloria Inés Mosquera'],
            ['Catequista rotativo (CR)', 'Yolanda Paz'],
        ];

        foreach ($cargos as $orden => [$cargo, $nombre]) {
            $retiro->rectorados()->create([
                'sexo'     => 'Femenino',
                'cargo'    => $cargo,
                'nombre'   => $nombre . ' (prueba)',
                'ciudad'   => 'Cali / ' . $this->elegir(self::BARRIOS),
                'telefono' => $this->celular(),
                'orden'    => $orden + 1,
            ]);
        }
    }



    //  Artículos típicos de cada equipo, con su historial de entradas y
    // salidas. Secretaría usa la lista de su Excel de inventario.
    private function inventarios(): void
    {
        $listas = [
            'Secretaría'      => [['Papelillo azul rey', 'paquetes'], ['Papelillo amarillo', 'paquetes'], ['Papelillo blanco', 'paquetes'], ['Bolsa industrial grande', 'unidades'], ['Papel celofán', 'paquetes'], ['Cartulina pliego', 'pliegos'], ['Marcador rojo', 'unidades'], ['Marcador negro', 'unidades'], ['Lápices', 'cajas'], ['Esferos', 'cajas'], ['Colores', 'cajas'], ['Borradores', 'unidades'], ['Tajalápiz', 'unidades'], ['Velas', 'unidades'], ['Fósforos', 'cajas'], ['Hojas oficio', 'resmas'], ['Sobres de manila', 'unidades'], ['Cosedora', 'unidades']],
            'Cocina'          => [['Ollas grandes', 'unidades'], ['Sartenes', 'unidades'], ['Cucharones', 'unidades'], ['Arroz', 'kilos'], ['Aceite', 'litros'], ['Sal', 'kilos'], ['Azúcar', 'kilos'], ['Platos desechables', 'paquetes'], ['Vasos desechables', 'paquetes'], ['Servilletas', 'paquetes'], ['Bandejas', 'unidades'], ['Delantales', 'unidades']],
            'Cafetería'       => [['Termos de café', 'unidades'], ['Café molido', 'kilos'], ['Aromática', 'cajas'], ['Mezcladores', 'paquetes'], ['Vasos de cartón', 'paquetes'], ['Galletas', 'paquetes'], ['Azúcar en sobres', 'cajas'], ['Greca', 'unidades']],
            'Floristería'     => [['Rosas rojas', 'unidades'], ['Rosas blancas', 'unidades'], ['Bombas', 'paquetes'], ['Cinta de regalo', 'rollos'], ['Papel seda', 'paquetes'], ['Floreros', 'unidades'], ['Tijeras', 'unidades']],
            'Librería'        => [['Camisetas talla S', 'unidades'], ['Camisetas talla M', 'unidades'], ['Camisetas talla L', 'unidades'], ['Camisetas talla XL', 'unidades'], ['Crucifijos', 'unidades'], ['Camándulas', 'unidades'], ['Biblias', 'unidades'], ['Libros de oraciones', 'unidades'], ['Faldas', 'unidades']],
            'Montaje'         => [['Extensiones eléctricas', 'unidades'], ['Escobas', 'unidades'], ['Traperos', 'unidades'], ['Papel higiénico', 'paquetes'], ['Jabón líquido', 'litros'], ['Bolsas de basura', 'paquetes'], ['Cinta de enmascarar', 'rollos'], ['Mesas plásticas', 'unidades'], ['Sillas plásticas', 'unidades'], ['Cuadro de Jesús', 'unidades']],
            'Previstos'       => [['Velas grandes', 'unidades'], ['Cirios', 'unidades'], ['Rosarios', 'unidades'], ['Hojas de cantos', 'paquetes']],
            'Coro'            => [['Guitarras', 'unidades'], ['Cuerdas de guitarra', 'juegos'], ['Atriles', 'unidades'], ['Cancioneros', 'unidades'], ['Micrófonos', 'unidades']],
            'Tesorería'       => [['Talonario de recibos', 'unidades'], ['Caja para la ofrenda', 'unidades'], ['Sobres', 'paquetes']],
            'Directores'      => [['Carpetas del rectorado', 'unidades'], ['Libro de charlas', 'unidades'], ['Reloj de sala', 'unidades']],
            'Premontaje'      => [['Lonas', 'unidades'], ['Cuerdas', 'rollos'], ['Herramientas', 'cajas']],
            'Junta Diocesana' => [['Carnets', 'paquetes'], ['Insignias', 'unidades']],
        ];

        $usuarios = Usuario::pluck('id')->all();

        foreach ($listas as $nombreEquipo => $articulos) {
            $equipo = Equipo::where('nombre', $nombreEquipo)->first();

            if (! $equipo) {
                continue;
            }

            foreach ($articulos as [$nombre, $unidad]) {
                $articulo = Articulo::firstOrCreate(
                    ['equipo_id' => $equipo->id, 'nombre' => $nombre],
                    ['unidad' => $unidad, 'cantidad' => 0, 'por_comprar' => 0]
                );

                if ($articulo->movimientos()->exists()) {
                    continue;
                }

                // Entrada inicial, unas salidas y a veces algo por comprar o dañado
                $cantidad = mt_rand(0, 100) <= 8 ? 0 : mt_rand(2, 60);
                $this->movimiento($articulo, MovimientoInventario::ENTRADA, max($cantidad, 1), 'Cantidad inicial ' . self::MARCA, $usuarios);

                for ($s = 0, $salidas = mt_rand(0, 3); $s < $salidas && $articulo->cantidad > 1; $s++) {
                    $this->movimiento($articulo, MovimientoInventario::SALIDA, mt_rand(1, (int) ceil($articulo->cantidad / 3)), $this->elegir(['Para la sala', 'Retiro de noviembre', null]), $usuarios);
                }

                if ($cantidad === 0) {
                    $this->movimiento($articulo, MovimientoInventario::AJUSTE, 0, 'Se acabó', $usuarios);
                }

                $articulo->update([
                    'por_comprar' => $articulo->cantidad < 5 ? mt_rand(5, 20) : 0,
                    'estado'      => mt_rand(1, 100) <= 10 ? $this->elegir(['Regular', 'Dañado']) : null,
                ]);
            }
        }
    }



    private function movimiento(Articulo $articulo, string $tipo, int $cantidad, ?string $nota, array $usuarios): void
    {
        $nueva = match ($tipo) {
            MovimientoInventario::ENTRADA => $articulo->cantidad + $cantidad,
            MovimientoInventario::SALIDA  => $articulo->cantidad - $cantidad,
            default                       => $cantidad,
        };

        $articulo->update(['cantidad' => $nueva]);
        $articulo->movimientos()->create([
            'tipo'                => $tipo,
            'cantidad'            => $cantidad,
            'cantidad_resultante' => $nueva,
            'nota'                => $nota,
            'usuario_id'          => $this->elegir($usuarios),
        ]);
    }



    private function elegir(array $opciones): mixed
    {
        $opciones = array_values($opciones);

        return $opciones[mt_rand(0, count($opciones) - 1)];
    }



    private function celular(): string
    {
        return '3' . mt_rand(0, 2) . mt_rand(0, 9) . ' ' . mt_rand(100, 999) . ' ' . mt_rand(1000, 9999);
    }
}
