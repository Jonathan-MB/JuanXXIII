<?php

namespace App\Mail;

use App\Models\Ajuste;
use App\Models\Inscripcion;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ConfirmacionInscripcion extends Mailable
{
    public function __construct(public Inscripcion $inscripcion)
    {
    }



    public function envelope(): Envelope
    {
        return new Envelope(subject: Ajuste::valor(Ajuste::CORREO_ASUNTO));
    }



    //  El texto se escribe en Ajustes; aquí solo se cambian las palabras
    // entre llaves por los datos de la persona y del retiro.
    public function content(): Content
    {
        return new Content(view: 'correos.confirmacion', with: [
            'parrafos' => preg_split('/\n\s*\n/', trim(self::textoPara($this->inscripcion))),
        ]);
    }



    public static function textoPara(Inscripcion $inscripcion): string
    {
        $persona = $inscripcion->persona;
        $retiro  = $inscripcion->retiro;

        return strtr(Ajuste::valor(Ajuste::CORREO_TEXTO), [
            '{nombre}'    => $persona->nombres,
            '{retiro}'    => ($persona->sexo === 'Femenino' ? 'Retiro de mujeres N.º ' : 'Retiro de hombres N.º ') . $retiro->numeroPara($persona->sexo),
            '{fechas}'    => $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year,
            '{lugar}'     => $retiro->lugar,
            '{direccion}' => $retiro->direccion,
            '{hora}'      => Carbon::parse($retiro->hora_inicio)->format('g:i a'),
        ]);
    }
}
