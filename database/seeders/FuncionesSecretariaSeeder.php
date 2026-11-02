<?php

namespace Database\Seeders;

use App\Models\TareaCronograma;
use Illuminate\Database\Seeder;

class FuncionesSecretariaSeeder extends Seeder
{
    public const RESPONSABLE = 'Secretaría';

    //  La sección o impresión que necesita cada tarea (clave de
    // TareaCronograma::ENLACES), buscada por el comienzo del texto.
    public const ENLACES = [
        'Preparar las carpetas del rectorado'                 => 'impresiones',
        'Montar las salas'                                    => 'archivosLetreros',
        'Separar las dinámicas'                               => 'documentos',
        'Hacer la lista de integrantes de cada equipo'        => 'santisimo',
        'Enviar a sala la dinámica'                           => 'documentos',
        'Imprimir la lista de invitados (Correcciones)'       => 'imprimirCorrecciones',
        'Decorar los sobres de manila'                        => 'imprimirSobres',
        'Verificar y ubicar la rotación al Santísimo'         => 'imprimirSantisimo',
        'Enviar las listas de correcciones'                   => 'imprimirCorrecciones',
        'Pasar las correcciones a la app'                     => 'inscritos',
        'Entregar al dirigente de Librería'                   => 'imprimirCamisetas',
        'Marcar los sobres de manila'                         => 'imprimirSobres',
        'Recoger los materiales, hacer el inventario'         => 'imprimirInventario',
    ];



    public function run(): void
    {
        $this->crearTareas();
        $this->ponerEnlaces();
    }



    //  Solo llena el enlace de las tareas que no tienen uno: si alguien lo
    // cambió o lo quitó desde Cronograma, se respeta.
    private function ponerEnlaces(): void
    {
        foreach (self::ENLACES as $comienzo => $enlace) {
            TareaCronograma::whereNull('enlace')
                ->where('descripcion', 'like', $comienzo . '%')
                ->update(['enlace' => $enlace]);
        }
    }



    private function crearTareas(): void
    {
        //  Se editan desde Cronograma: si ya están cargadas no se toca nada,
        // para no revivir una tarea que se quitó o se cambió al actualizar
        // el servidor. Las de "(prueba)" no cuentan.
        $yaCargadas = TareaCronograma::where('responsable', self::RESPONSABLE)
            ->where('descripcion', 'not like', '%(prueba)')
            ->exists();

        if ($yaCargadas) {
            return;
        }

        //  Sacadas del PDF "Funciones del equipo de Secretaría". La hora es
        // el límite para tenerla lista, calculada con el desarrollo del
        // retiro de hombres (el de mujeres va un poco antes).
        $tareas = [
            // Viernes
            [0, '15:00', 'Organizar el equipo: oración inicial, forma de trabajo y pautas (sin celular, silencio, orden y obediencia)'],
            [0, '16:00', 'Preparar materiales de sala: cartulina por mesa, 2 hojas oficio, oración al Espíritu Santo y lápiz por invitado, esfero por catequista; colores, borrador, tajalápiz, vela y fósforos por mesa'],
            [0, '16:30', 'Preparar las carpetas del rectorado: formatos del dirigente, del rector(a) y del vicerrector(a), y sus materiales (cosedora, perforadora, saca-ganchos, esferos, resaltadores)'],
            [0, '19:00', 'Montar las salas: material en cada mesa, cuadro de Jesús, letreros (Ilusión-Entrega-Espíritu de Caridad a la derecha, Piedad-Estudio-Acción a la izquierda) y reloj'],
            [0, '19:15', 'Separar las dinámicas de todo el retiro por sala y entregarlas a los rectorados'],
            [0, '20:00', 'Hacer la lista de integrantes de cada equipo para la rotación al Santísimo de equipos'],
            [0, '23:00', 'Enviar a sala la dinámica Conócete (1 por invitado)'],
            [0, '23:30', 'Imprimir la lista de invitados (Correcciones) para entregarla a primera hora del sábado'],
            [0, '23:45', 'Enviar a sala la dinámica Hijo pródigo (2 por mesa)'],
            [0, '23:50', 'Decorar los sobres de manila de Ayudas de Dios, si no se terminaron antes'],

            // Sábado
            [1, '06:30', 'Verificar y ubicar la rotación al Santísimo de equipos'],
            [1, '07:45', 'Enviar a sala la dinámica Ideal (2 por mesa) y un paquete de bananas'],
            [1, '08:30', 'Enviar las listas de correcciones a cada sala y pedir que las devuelvan pronto'],
            [1, '10:00', 'Tras el primer pase de sala: ubicar las canastas de ayudas con las listas, el horario de recepción y el aviso de lo que NO se recibe'],
            [1, '11:00', 'Pasar las correcciones a la app y, si hay tiempo, reenviar las listas a sala'],
            [1, '12:00', 'Entregar al dirigente de Librería el listado de tallas de camisetas y acordar la entrega'],
            [1, '13:30', 'Enviar a sala la dinámica Sacramentos (1 por invitado)'],
            [1, '14:00', 'Marcar los sobres de manila con las listas ya corregidas'],
            [1, '15:00', 'Conseguir apoyo para revisar las cartas (por ejemplo de Previstos), si hace falta'],
            [1, '16:00', 'Recoger las canastas de ayudas, hacer oración y revisar las cartas a puertas cerradas, con total reserva'],
            [1, '17:00', 'Recoger en Librería las camisetas y acordar la entrega de cristos y crucifijos'],
            [1, '18:15', 'Empacar los sobres de Ayudas de Dios (cartas y camiseta) y, con permiso del rector, llevarlos a sala'],
            [1, '18:30', 'Guardar en un sobre las cartas que no pasaron, para que el dirigente de Secretaría las revise'],
            [1, '20:05', 'Todo el equipo a la plenaria de equipos'],
            [1, '22:00', 'Llenar los carnets para que los rectores los firmen'],
            [1, '23:00', 'Empezar la correspondencia de catequistas y rectorados'],

            // Domingo
            [2, '06:30', 'Enviar a sala la dinámica Iglesia y sus figuras (1 por invitado) y un paquete de bananas'],
            [2, '07:00', 'Llevar los carnets a sala para que los rectores los firmen'],
            [2, '09:00', 'Terminar la correspondencia de catequistas y rectorados (un detalle a quien no tenga carta)'],
            [2, '10:00', 'Empacar y marcar por mesa los rosarios y los cristos'],
            [2, '11:00', 'Pedir a cada rector los carnets firmados'],
            [2, '13:00', 'Entregar a los rectores, antes de Apóstoles, el paquete de cada mesa: carnets, cristos, rosarios, listas corregidas, formato de cuartos días y dulces'],
            [2, '13:00', 'Llevar a sala la correspondencia de catequistas y rectorados, con dulces para el rectorado'],
            [2, '16:00', 'Recoger los materiales, hacer el inventario de Secretaría y entregar copia al dirigente diocesano'],
        ];

        foreach ($tareas as [$dia, $hora, $descripcion]) {
            TareaCronograma::firstOrCreate(
                ['descripcion' => $descripcion],
                ['dia_relativo' => $dia, 'hora' => $hora, 'responsable' => self::RESPONSABLE]
            );
        }
    }
}
