<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ajuste extends Model
{

    // Las claves que existen; cada una es una fila de la tabla
    public const WHATSAPP          = 'whatsapp_enlace';
    public const WHATSAPP_QR       = 'whatsapp_qr';
    public const CORREO_ASUNTO     = 'correo_confirmacion_asunto';
    public const CORREO_TEXTO      = 'correo_confirmacion_texto';
    public const CORREO_AUTOMATICO = 'correo_confirmacion_automatico';

    //  El letrero de Nequi. Vacíos: se usa la cuenta Nequi del retiro
    public const NEQUI_NUMERO      = 'letrero_nequi_numero';
    public const NEQUI_TITULAR     = 'letrero_nequi_titular';

    //  El rosario por Google Meet de la página pública. Sin enlace no
    // sale ni el botón ni la sección.
    public const ROSARIO_ENLACE    = 'rosario_enlace';
    public const ROSARIO_DIAS      = 'rosario_dias';
    public const ROSARIO_HORA      = 'rosario_hora';
    public const ROSARIO_PEDIDOS   = 'rosario_pedidos';

    //  El Cuarto día: no es una perseverancia, solo sale en el directorio.
    // Los tres pueden quedar vacíos (en el directorio quedan para escribir).
    public const CUARTO_DIA_FECHA  = 'cuarto_dia_fecha';
    public const CUARTO_DIA_HORA   = 'cuarto_dia_hora';
    public const CUARTO_DIA_LUGAR  = 'cuarto_dia_lugar';

    //  Hasta qué versión de la lista de tareas se cargó (CronogramaSeeder)
    public const CRONOGRAMA_VERSION = 'cronograma_version';

    //  Lo que se usa mientras nadie lo haya cambiado. En el texto, lo que
    // va entre llaves se reemplaza con los datos de cada persona.
    public const POR_DEFECTO = [
        self::ROSARIO_ENLACE    => 'https://meet.google.com/iha-zxwk-gzk',
        self::ROSARIO_DIAS      => 'Lunes a sábado',
        self::ROSARIO_HORA      => '5:30 a. m.',
        self::ROSARIO_PEDIDOS   => '3136441569',
        self::CORREO_ASUNTO     => 'Confirmación de tu cupo · Retiro Juan XXIII',
        self::CORREO_TEXTO      => "Hola, {nombre}:\n\nTe confirmamos tu cupo en el {retiro} del Movimiento de Retiros Parroquiales Juan XXIII, que se vive el {fechas} en {lugar} ({direccion}).\n\nTe esperamos el primer día a las {hora}. Recuerda que no se lleva celular.\n\nSi tienes alguna duda, comunícate con los contactos de informes.\n\nDios te bendiga.",
        self::CORREO_AUTOMATICO => '0',
    ];

    protected $fillable = [
        'clave',
        'valor',
    ];



    public static function valor(string $clave): ?string
    {
        return self::where('clave', $clave)->value('valor') ?? (self::POR_DEFECTO[$clave] ?? null);
    }
}
