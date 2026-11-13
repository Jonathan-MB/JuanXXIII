<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use App\Models\TareaCronograma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CronogramaSeeder extends Seeder
{
    //  Sube cada vez que se agregan tareas o cambian enlaces. Cada versión
    // se aplica una sola vez: lo que después se quite o se cambie desde
    // Cronograma no vuelve a aparecer al correr db:seed.
    public const VERSION = 2;

    //  [día, hora, descripción, responsable, enlace, versión en que llegó].
    // Día: 0 viernes, 1 sábado, 2 domingo; negativo = días antes. La hora
    // es el límite para tenerla lista, con el desarrollo de los hombres
    // (el de las mujeres va un poco antes). Enlace: TareaCronograma::ENLACES.
    // Versión 1: el PDF "Funciones del equipo de Secretaría". Versión 2: los
    // pasos que faltaban (pagos, impresiones, llegada, llamado y cierre).
    private const TAREAS = [
        // Días antes
        [-3, '18:00', 'Revisar los inscritos: que tengan cédula, celular, fecha de nacimiento, talla y padrino', 'Secretaría', 'inscritos', 2],
        [-3, '18:00', 'Revisar los pagos y registrar los comprobantes que falten', 'Tesorería', 'pagos', 2],
        [-3, '19:00', 'Decorar los sobres de manila de Ayudas de Dios (uno por invitado)', 'Secretaría', null, 2],
        [-2, '18:00', 'Crear las mesas de hombres y de mujeres (número y nombre de cada mesa)', 'Secretaría', 'mesas', 2],
        [-2, '19:00', 'Imprimir la lista de llegada (para marcar a mano quién llega)', 'Secretaría', 'imprimirLlegada', 2],
        [-2, '19:00', 'Imprimir las hojas de mesa en blanco', 'Secretaría', 'imprimirMesasEnBlanco', 2],
        [-2, '19:30', 'Imprimir los letreros de la casa: silencio, baños, Nequi, Ayudas de Dios, bebidas…', 'Secretaría', 'letreros', 2],
        [-2, '19:30', 'Imprimir los letreros de número de mesa', 'Secretaría', 'imprimirLetrerosMesa', 2],
        [-2, '20:00', 'Imprimir las tarjetas De / Para para Floristería', 'Secretaría', 'imprimirFlores', 2],
        [-1, '16:00', 'Confirmar los pagos pendientes con los padrinos', 'Tesorería', 'pagos', 2],
        [-1, '18:00', 'Imprimir el desarrollo del retiro: 2 para el rector(a), 1 para el vicerrector(a) y 1 para el dirigente', 'Secretaría', 'imprimirDesarrollo', 2],
        [-1, '18:30', 'Imprimir la carpeta del rector(a)', 'Secretaría', 'imprimirCarpetaRector', 2],
        [-1, '18:30', 'Imprimir la carpeta del vicerrector(a)', 'Secretaría', 'imprimirCarpetaVicerrector', 2],
        [-1, '18:30', 'Imprimir la carpeta del dirigente', 'Secretaría', 'imprimirCarpetaDirigente', 2],
        [-1, '20:00', 'Cerrar las inscripciones del retiro', 'Secretaría', 'retiros', 2],

        // Viernes
        [0, '12:00', 'Imprimir la lista de pagos para cobrar en la llegada (quién debe y cuánto)', 'Tesorería', 'imprimirPagos', 2],
        [0, '13:30', 'Llegar a la casa de retiro y ubicar el lugar de Secretaría', 'Secretaría', null, 2],
        [0, '14:00', 'Recibir a los invitados: marcar en la lista quién llegó', 'Secretaría', 'llegada', 2],
        [0, '14:00', 'Cobrar en la llegada a quien debe y marcar en la lista impresa quién terminó de pagar', 'Tesorería', 'pagos', 2],
        [0, '15:00', 'Organizar el equipo: oración inicial, forma de trabajo y pautas (sin celular, silencio, orden y obediencia)', 'Secretaría', null, 1],
        [0, '15:00', 'Reunión con catequistas', 'Rectorado', null, 2],
        [0, '16:00', 'Preparar materiales de sala: cartulina por mesa, 2 hojas oficio, oración al Espíritu Santo y lápiz por invitado, esfero por catequista; colores, borrador, tajalápiz, vela y fósforos por mesa', 'Secretaría', null, 1],
        [0, '16:30', 'Preparar las carpetas del rectorado: formatos del dirigente, del rector(a) y del vicerrector(a), y sus materiales (cosedora, perforadora, saca-ganchos, esferos, resaltadores)', 'Secretaría', 'impresiones', 1],
        [0, '16:45', 'Cerrar la llegada: revisar en Llegada quién falta por llegar y avisar al rector(a)', 'Secretaría', 'llegada', 2],
        [0, '18:00', 'Registrar en la app los pagos recibidos en la llegada y revisar quién sigue debiendo', 'Tesorería', 'pagos', 2],
        [0, '18:15', 'Imprimir la lista de llamado y entregarla al rector(a) (las mujeres se llaman primero)', 'Secretaría', 'imprimirLlamado', 2],
        [0, '19:00', 'Montar las salas: material en cada mesa, cuadro de Jesús, letreros (Ilusión-Entrega-Espíritu de Caridad a la derecha, Piedad-Estudio-Acción a la izquierda) y reloj', 'Secretaría', 'imprimirLetrerosMesa', 1],
        [0, '19:15', 'Llamado de los invitados a sala', 'Rectorado', 'imprimirLlamado', 2],
        [0, '19:15', 'Separar las dinámicas de todo el retiro por sala y entregarlas a los rectorados', 'Secretaría', 'documentos', 1],
        [0, '19:45', 'Enviar a sala las hojas de mesa en blanco para que cada mesa escriba quién se sienta en cada silla', 'Secretaría', 'imprimirMesasEnBlanco', 2],
        [0, '20:00', 'Hacer la lista de integrantes de cada equipo para la rotación al Santísimo de equipos', 'Secretaría', 'santisimo', 1],
        [0, '21:30', 'Pasar a la app la mesa y la silla de cada invitado (con las hojas de mesa)', 'Secretaría', 'llegada', 2],
        [0, '22:00', 'Escribir en Mesas el CT y el CA de cada mesa', 'Secretaría', 'mesas', 2],
        [0, '22:30', 'Imprimir las hojas de ubicación (una por mesa) para el rectorado', 'Secretaría', 'imprimirUbicacion', 2],
        [0, '23:00', 'Enviar a sala la dinámica Conócete (1 por invitado)', 'Secretaría', 'documentos', 1],
        [0, '23:30', 'Imprimir la lista de invitados (Correcciones) para entregarla a primera hora del sábado', 'Secretaría', 'imprimirCorrecciones', 1],
        [0, '23:45', 'Enviar a sala la dinámica Hijo pródigo (2 por mesa)', 'Secretaría', 'documentos', 1],
        [0, '23:50', 'Decorar los sobres de manila de Ayudas de Dios, si no se terminaron antes', 'Secretaría', null, 1],

        // Sábado
        [1, '06:30', 'Verificar y ubicar la rotación al Santísimo de equipos', 'Secretaría', 'imprimirSantisimo', 1],
        [1, '07:45', 'Enviar a sala la dinámica Ideal (2 por mesa) y un paquete de bananas', 'Secretaría', 'documentos', 1],
        [1, '08:30', 'Enviar las listas de correcciones a cada sala y pedir que las devuelvan pronto', 'Secretaría', 'imprimirCorrecciones', 1],
        [1, '10:00', 'Tras el primer pase de sala: ubicar las canastas de ayudas con las listas, el horario de recepción y el aviso de lo que NO se recibe', 'Secretaría', 'letreros', 1],
        [1, '11:00', 'Pasar las correcciones a la app y, si hay tiempo, reenviar las listas a sala', 'Secretaría', 'inscritos', 1],
        [1, '12:00', 'Entregar al dirigente de Librería el listado de tallas de camisetas y acordar la entrega', 'Secretaría', 'imprimirCamisetas', 1],
        [1, '13:30', 'Enviar a sala la dinámica Sacramentos (1 por invitado)', 'Secretaría', 'documentos', 1],
        [1, '14:00', 'Marcar los sobres de manila con las listas ya corregidas', 'Secretaría', 'imprimirSobres', 1],
        [1, '15:00', 'Conseguir apoyo para revisar las cartas (por ejemplo de Previstos), si hace falta', 'Secretaría', null, 1],
        [1, '16:00', 'Recoger las canastas de ayudas, hacer oración y revisar las cartas a puertas cerradas, con total reserva', 'Secretaría', null, 1],
        [1, '17:00', 'Recoger en Librería las camisetas y acordar la entrega de cristos y crucifijos', 'Secretaría', null, 1],
        [1, '18:00', 'Hacer las llamadas a los familiares con la hoja de llamadas', 'Secretaría', 'imprimirLlamadas', 2],
        [1, '18:15', 'Empacar los sobres de Ayudas de Dios (cartas y camiseta) y, con permiso del rector, llevarlos a sala', 'Secretaría', null, 1],
        [1, '18:30', 'Guardar en un sobre las cartas que no pasaron, para que el dirigente de Secretaría las revise', 'Secretaría', null, 1],
        [1, '20:05', 'Todo el equipo a la plenaria de equipos', 'Secretaría', null, 1],
        [1, '22:00', 'Llenar los carnets para que los rectores los firmen', 'Secretaría', null, 1],
        [1, '22:30', 'Revisar los datos del directorio y de las cartas: rectorado, firmas, perseverancias y grupo de WhatsApp', 'Secretaría', 'datosImpresion', 2],
        [1, '23:00', 'Empezar la correspondencia de catequistas y rectorados', 'Secretaría', null, 1],

        // Domingo
        [2, '06:30', 'Enviar a sala la dinámica Iglesia y sus figuras (1 por invitado) y un paquete de bananas', 'Secretaría', 'documentos', 1],
        [2, '07:00', 'Llevar los carnets a sala para que los rectores los firmen', 'Secretaría', null, 1],
        [2, '07:30', 'Marcar en Llegada que terminaron el retiro (todos los que tienen mesa y silla)', 'Secretaría', 'llegada', 2],
        [2, '08:00', 'Imprimir el directorio ya corregido (una copia por invitado y catequista)', 'Secretaría', 'imprimirDirectorio', 2],
        [2, '09:00', 'Imprimir las cartas al presbítero', 'Secretaría', 'imprimirCarta', 2],
        [2, '09:00', 'Terminar la correspondencia de catequistas y rectorados (un detalle a quien no tenga carta)', 'Secretaría', null, 1],
        [2, '10:00', 'Empacar y marcar por mesa los rosarios y los cristos', 'Secretaría', null, 1],
        [2, '11:00', 'Pedir a cada rector los carnets firmados', 'Secretaría', null, 1],
        [2, '13:00', 'Entregar a los rectores, antes de Apóstoles, el paquete de cada mesa: carnets, cristos, rosarios, listas corregidas, formato de cuartos días y dulces', 'Secretaría', 'imprimirDirectorio', 1],
        [2, '13:00', 'Llevar a sala la correspondencia de catequistas y rectorados, con dulces para el rectorado', 'Secretaría', null, 1],
        [2, '14:30', 'Imprimir la lista para familiares y entregarla en la entrada de la clausura', 'Secretaría', 'imprimirFamiliares', 2],
        [2, '14:30', 'Imprimir el listado de invitados para puertas', 'Secretaría', 'imprimirPuertas', 2],
        [2, '16:00', 'Recoger los materiales, hacer el inventario de Secretaría y entregar copia al dirigente diocesano', 'Secretaría', 'imprimirInventario', 1],
    ];

    //  Enlaces de tareas que ya existían y cambiaron en cada versión:
    // comienzo del texto => [enlace de antes, enlace nuevo]. Solo se
    // cambian si siguen con el de antes (si alguien lo editó, se respeta).
    private const CAMBIOS = [
        2 => [
            'Montar las salas'                                      => ['archivosLetreros', 'imprimirLetrerosMesa'],
            'Decorar los sobres de manila de Ayudas de Dios, si no' => ['imprimirSobres', null],
            'Tras el primer pase de sala'                           => [null, 'letreros'],
            'Entregar a los rectores, antes de Apóstoles'           => [null, 'imprimirDirectorio'],
        ],
    ];



    public function run(): void
    {
        $actual = $this->versionActual();

        if ($actual >= self::VERSION) {
            return;
        }

        DB::transaction(function () use ($actual) {
            foreach (self::TAREAS as [$dia, $hora, $descripcion, $responsable, $enlace, $version]) {
                if ($version > $actual) {
                    TareaCronograma::firstOrCreate(
                        ['descripcion' => $descripcion],
                        ['dia_relativo' => $dia, 'hora' => $hora, 'responsable' => $responsable, 'enlace' => $enlace]
                    );
                }
            }

            foreach (self::CAMBIOS as $version => $cambios) {
                if ($version <= $actual) {
                    continue;
                }

                foreach ($cambios as $comienzo => [$antes, $despues]) {
                    TareaCronograma::where('descripcion', 'like', $comienzo . '%')
                        ->when($antes === null, fn ($consulta) => $consulta->whereNull('enlace'), fn ($consulta) => $consulta->where('enlace', $antes))
                        ->update(['enlace' => $despues]);
                }
            }

            Ajuste::updateOrCreate(['clave' => Ajuste::CRONOGRAMA_VERSION], ['valor' => (string) self::VERSION]);
        });
    }



    //  Sin la marca guardada: si ya hay tareas (sin contar las de prueba)
    // es porque se cargó la versión 1, la del PDF de Secretaría.
    private function versionActual(): int
    {
        $guardada = Ajuste::valor(Ajuste::CRONOGRAMA_VERSION);

        if ($guardada !== null) {
            return (int) $guardada;
        }

        return TareaCronograma::where('descripcion', 'not like', '%(prueba)')->exists() ? 1 : 0;
    }
}
