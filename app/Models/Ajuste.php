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

    //  Lo que se usa mientras nadie lo haya cambiado. En el texto, lo que
    // va entre llaves se reemplaza con los datos de cada persona.
    public const POR_DEFECTO = [
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
