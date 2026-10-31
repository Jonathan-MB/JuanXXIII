<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Retiro;

class ExportacionController extends Controller
{

    //  Todas las inscripciones del retiro actual, también las de quien no
    // fue o abandonó, con los datos completos: sirve de respaldo y para
    // seguir trabajando en Excel si hace falta.
    public function inscritos()
    {
        $retiro = $this->retiroOFallar();

        $inscripciones = Inscripcion::with(['persona', 'mesa', 'contactosEmergencia'])
            ->withSum('pagos', 'valor')
            ->where('retiro_id', $retiro->id)
            ->get()
            ->sortBy(fn ($inscripcion) => [$inscripcion->persona->sexo, $inscripcion->persona->apellidos, $inscripcion->persona->nombres])
            ->values();

        $encabezados = [
            'Retiro', 'Sexo', 'Estado', 'Apellidos', 'Nombres', 'Cédula', 'Fecha de nacimiento', 'Edad',
            'Celular', 'Correo', 'Dirección', 'Barrio', 'Ciudad', 'EPS', 'Medicamentos', 'Talla',
            'Bautismo', 'Primera comunión', 'Confirmación', 'Matrimonio',
            'Mesa', 'Silla', 'Llegada',
            'Padrino', 'Teléfono del padrino', 'Padrino presente', 'Parroquia del padrino',
            'Contacto 1', 'Parentesco 1', 'Celular contacto 1', 'Contacto 2', 'Parentesco 2', 'Celular contacto 2',
            'Forma de pago', 'Ofrenda', 'Pagado', 'Saldo', 'Inscrito el',
        ];

        $filas = $inscripciones->map(function ($inscripcion) use ($retiro) {
            $persona   = $inscripcion->persona;
            $contactos = $inscripcion->contactosEmergencia->values();

            return [
                $retiro->numeroPara($persona->sexo),
                $persona->sexo === 'Femenino' ? 'Mujer' : 'Hombre',
                $inscripcion->nombre_estado,
                $persona->apellidos,
                $persona->nombres,
                $persona->cedula,
                $persona->fecha_nacimiento?->format('d/m/Y'),
                $persona->fecha_nacimiento ? (int) $persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) : null,
                $persona->celular,
                $persona->correo,
                $persona->direccion,
                $persona->barrio,
                $persona->ciudad,
                $persona->eps,
                $persona->medicamentos,
                $persona->talla_camiseta,
                $persona->bautismo ? 'Sí' : 'No',
                $persona->primera_comunion ? 'Sí' : 'No',
                $persona->confirmacion ? 'Sí' : 'No',
                $persona->matrimonio ? 'Sí' : 'No',
                $inscripcion->mesa?->numero,
                $inscripcion->silla,
                $inscripcion->llegada_en?->format('d/m/Y g:i a'),
                $inscripcion->tiene_padrino ? $inscripcion->padrino_nombre : 'No tiene',
                $inscripcion->padrino_telefono,
                $inscripcion->padrino_presente,
                $inscripcion->padrino_parroquia,
                $contactos[0]->nombre ?? null,
                $contactos[0]->parentesco ?? null,
                $contactos[0]->celular ?? null,
                $contactos[1]->nombre ?? null,
                $contactos[1]->parentesco ?? null,
                $contactos[1]->celular ?? null,
                $inscripcion->forma_pago,
                (int) $retiro->ofrenda,
                (int) $inscripcion->total_pagado,
                (int) $inscripcion->saldo,
                $inscripcion->created_at?->format('d/m/Y'),
            ];
        });

        return $this->descargar('inscritos-retiro-' . $retiro->numero_hombres . 'H-' . $retiro->numero_mujeres . 'M.csv', $encabezados, $filas);
    }



    // Cada pago o abono del retiro actual, uno por fila
    public function pagos()
    {
        $retiro = $this->retiroOFallar();

        $pagos = Pago::with(['inscripcion.persona', 'usuario'])
            ->whereHas('inscripcion', fn ($consulta) => $consulta->where('retiro_id', $retiro->id))
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $encabezados = ['Fecha', 'Apellidos', 'Nombres', 'Cédula', 'Sexo', 'Estado de la inscripción', 'Valor', 'Medio', 'Nota', 'Registró', 'Comprobante'];

        $filas = $pagos->map(fn ($pago) => [
            $pago->fecha?->format('d/m/Y'),
            $pago->inscripcion->persona->apellidos,
            $pago->inscripcion->persona->nombres,
            $pago->inscripcion->persona->cedula,
            $pago->inscripcion->persona->sexo === 'Femenino' ? 'Mujer' : 'Hombre',
            $pago->inscripcion->nombre_estado,
            (int) $pago->valor,
            $pago->medio,
            $pago->nota,
            $pago->usuario?->nombre,
            $pago->comprobante ? 'Sí' : 'No',
        ]);

        return $this->descargar('pagos-retiro-' . $retiro->numero_hombres . 'H-' . $retiro->numero_mujeres . 'M.csv', $encabezados, $filas);
    }



    //  CSV que Excel en español abre bien: separado por punto y coma y
    // con BOM para que respete las tildes.
    private function descargar(string $nombre, array $encabezados, $filas)
    {
        return response()->streamDownload(function () use ($encabezados, $filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, $encabezados, ';');

            foreach ($filas as $fila) {
                fputcsv($salida, array_map(fn ($valor) => $this->celdaSegura($valor), $fila), ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }



    //  Un texto que empieza por =, +, - o @ Excel lo toma como fórmula:
    // alguien podría escribir una en su nombre al inscribirse. Se le
    // antepone un apóstrofo para que quede como texto. Los números no.
    private function celdaSegura($valor)
    {
        if (! is_string($valor) || $valor === '') {
            return $valor;
        }

        return in_array($valor[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $valor : $valor;
    }



    private function retiroOFallar(): Retiro
    {
        $retiro = Retiro::actual();
        abort_if(! $retiro, 404, 'No hay un retiro próximo.');

        return $retiro;
    }
}
