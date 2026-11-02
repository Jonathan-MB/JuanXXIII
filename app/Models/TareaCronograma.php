<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TareaCronograma extends Model
{

    // Una tarea se pone en rojo cuando falta esto o menos para su hora
    public const MINUTOS_ALERTA = 60;

    //  A dónde puede llevar una tarea: clave => [texto, ruta, parámetros].
    // Las que empiezan por "imprimir" se abren en otra pestaña.
    public const ENLACES = [
        'inscritos'                  => ['Ir a Inscritos', 'inscritos.index', []],
        'pagos'                      => ['Ir a Pagos', 'pagos.index', []],
        'llegada'                    => ['Ir a Llegada', 'llegada.index', []],
        'mesas'                      => ['Ir a Mesas', 'mesas.index', []],
        'desarrollo'                 => ['Ir a Desarrollo del retiro', 'desarrollo.index', []],
        'santisimo'                  => ['Ir a Santísimo (editar el horario)', 'santisimo.editar', []],
        'inventario'                 => ['Ir a Inventario', 'inventario.index', []],
        'impresiones'                => ['Ir a Impresiones', 'impresiones.index', []],
        'datosImpresion'             => ['Ir a Datos para imprimir (rectorado y firmas)', 'impresiones.datos', []],
        'documentos'                 => ['Ir a Documentos', 'archivos.index', ['categoria' => 'documentos']],
        'archivosCharlas'            => ['Ir a Charlas (archivos)', 'archivos.index', ['categoria' => 'charlas']],
        'archivosLetreros'           => ['Ir a Letreros (archivos)', 'archivos.index', ['categoria' => 'letreros']],
        'imprimirLlegada'            => ['Imprimir lista de llegada', 'impresiones.llegada', []],
        'imprimirMesasEnBlanco'      => ['Imprimir hojas de mesa en blanco', 'impresiones.mesasEnBlanco', []],
        'imprimirCarta'              => ['Imprimir cartas al presbítero', 'impresiones.carta', []],
        'imprimirCorrecciones'       => ['Imprimir hoja de correcciones', 'impresiones.correcciones', []],
        'imprimirDirectorio'         => ['Imprimir directorio', 'impresiones.directorio', []],
        'imprimirUbicacion'          => ['Imprimir hojas de ubicación', 'impresiones.ubicacion', []],
        'imprimirCamisetas'          => ['Imprimir camisetas por talla', 'impresiones.camisetas', []],
        'imprimirSobres'             => ['Imprimir rótulos para sobres', 'impresiones.sobres', []],
        'imprimirLlamadas'           => ['Imprimir llamadas familiares', 'impresiones.llamadas', []],
        'imprimirPrevistos'          => ['Imprimir entrega de previstos', 'impresiones.previstos', []],
        'imprimirCatequistas'        => ['Imprimir catequistas por mesa', 'impresiones.catequistas', []],
        'imprimirDesarrollo'         => ['Imprimir desarrollo del retiro', 'desarrollo.imprimir', []],
        'imprimirCharlistas'         => ['Imprimir lista de charlistas', 'desarrollo.imprimir', ['formato' => 'charlistas']],
        'imprimirSala'               => ['Imprimir rotación al Santísimo en sala', 'desarrollo.imprimir', ['formato' => 'sala']],
        'imprimirSantisimo'          => ['Imprimir rotación al Santísimo de equipos', 'santisimo.imprimir', []],
        'imprimirInventario'         => ['Imprimir inventario de todos los equipos', 'inventario.imprimirTodos', []],
        'imprimirCarpetaRector'      => ['Imprimir carpeta del rector(a)', 'impresiones.carpeta', ['cargo' => 'rector']],
        'imprimirCarpetaVicerrector' => ['Imprimir carpeta del vicerrector(a)', 'impresiones.carpeta', ['cargo' => 'vicerrector']],
        'imprimirCarpetaDirigente'   => ['Imprimir carpeta del dirigente', 'impresiones.carpeta', ['cargo' => 'dirigente']],
    ];

    protected $fillable = [
        'dia_relativo',
        'hora',
        'descripcion',
        'responsable',
        'enlace',
    ];

    protected $casts = [
        'dia_relativo' => 'integer',
    ];



    // "Viernes", "Sábado", "Domingo" o "3 días antes"
    public static function nombreDia(int $diaRelativo): string
    {
        return match (true) {
            $diaRelativo === 0 => 'Viernes',
            $diaRelativo === 1 => 'Sábado',
            $diaRelativo === 2 => 'Domingo',
            $diaRelativo === -1 => '1 día antes',
            default            => abs($diaRelativo) . ' días antes',
        };
    }



    // La fecha y hora exactas de la tarea en un retiro dado
    public function limiteEn(Retiro $retiro): Carbon
    {
        return $retiro->fecha_inicio->copy()
            ->addDays($this->dia_relativo)
            ->setTimeFromTimeString($this->hora);
    }



    //  El botón de la tarea en el inicio: texto, dirección y si se abre
    // en otra pestaña. Null si la tarea no lleva a ningún lado.
    public function destino(): ?object
    {
        if (! isset(self::ENLACES[$this->enlace])) {
            return null;
        }

        [$texto, $ruta, $parametros] = self::ENLACES[$this->enlace];

        return (object) [
            'texto'       => $texto,
            'url'         => route($ruta, $parametros),
            'otraPestana' => str_starts_with($this->enlace, 'imprimir'),
        ];
    }



    public function cumplidas(): HasMany
    {
        return $this->hasMany(TareaCumplida::class, 'tarea_cronograma_id');
    }
}
