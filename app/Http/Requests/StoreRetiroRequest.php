<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;

class StoreRetiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'numero_hombres'         => ['required', 'integer', 'min:1', 'unique:retiros,numero_hombres'],
            'numero_mujeres'         => ['required', 'integer', 'min:1', 'unique:retiros,numero_mujeres'],
            'fecha_inicio'           => ['required', 'date'],
            'hora_inicio'            => ['required', 'date_format:H:i'],
            'fecha_fin'              => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'hora_fin'               => ['required', 'date_format:H:i'],
            'ofrenda'                => ['required', 'numeric', 'min:0'],
            'que_incluye'            => ['nullable', 'string', 'max:150'],
            'porcentaje_reserva'     => ['required', 'integer', 'between:0,100'],
            'fecha_limite_pago'      => ['nullable', 'date', 'before_or_equal:fecha_inicio'],
            'lugar'                  => ['required', 'string', 'max:100'],
            'direccion'              => ['nullable', 'string', 'max:100'],
            'ciudad'                 => ['required', 'string', 'max:45'],
            'inscripciones_abiertas' => ['boolean'],

            'cuentas_pago'             => ['array'],
            'cuentas_pago.*.entidad'   => ['required', 'string', 'max:45'],
            'cuentas_pago.*.numero'    => ['required', 'string', 'max:20'],
            'cuentas_pago.*.titular'   => ['required', 'string', 'max:60'],

            'contactos_informes'           => ['array'],
            'contactos_informes.*.nombre'  => ['required', 'string', 'max:60'],
            'contactos_informes.*.celular' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'numero_hombres'         => $this->numeroHombres,
            'numero_mujeres'         => $this->numeroMujeres,
            'fecha_inicio'           => $this->fechaInicio,
            'hora_inicio'            => $this->horaInicio,
            'fecha_fin'              => $this->fechaFin,
            'hora_fin'               => $this->horaFin,
            'ofrenda'                => $this->limpiarValor($this->ofrenda),
            'que_incluye'            => $this->queIncluye,
            'porcentaje_reserva'     => $this->porcentajeReserva,
            'fecha_limite_pago'      => $this->fechaLimitePago,
            'inscripciones_abiertas' => $this->boolean('inscripcionesAbiertas'),
            'cuentas_pago'           => $this->filasLlenas($this->cuentasPago, ['numero', 'titular']),
            'contactos_informes'     => collect($this->filasLlenas($this->contactosInformes, ['nombre', 'celular']))
                ->map(fn ($fila) => array_merge($fila, ['celular' => Persona::normalizarTelefono($fila['celular'] ?? null)]))
                ->all(),
        ]);
    }



    public function messages(): array
    {
        return [
            'numero_hombres.required'               => 'Escribe el número del retiro de hombres.',
            'numero_hombres.unique'                 => 'Ya existe un retiro con ese número de hombres.',
            'numero_mujeres.required'               => 'Escribe el número del retiro de mujeres.',
            'numero_mujeres.unique'                 => 'Ya existe un retiro con ese número de mujeres.',
            'fecha_inicio.required'                 => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'                    => 'La fecha de cierre es obligatoria.',
            'fecha_fin.after_or_equal'              => 'La fecha de cierre no puede ser antes del inicio.',
            'hora_inicio.date_format'               => 'La hora de inicio no es válida.',
            'hora_fin.date_format'                  => 'La hora de cierre no es válida.',
            'ofrenda.required'                      => 'Escribe el valor de la ofrenda.',
            'ofrenda.numeric'                       => 'La ofrenda debe ser un número, sin letras.',
            'porcentaje_reserva.between'            => 'El porcentaje de reserva va de 0 a 100.',
            'fecha_limite_pago.before_or_equal'     => 'La fecha límite de pago debe ser antes del retiro.',
            'lugar.required'                        => 'Escribe el lugar del retiro.',
            'ciudad.required'                       => 'Escribe la ciudad.',
            'cuentas_pago.*.entidad.required'       => 'Cada cuenta de pago necesita la entidad (Nequi, banco...).',
            'cuentas_pago.*.numero.required'        => 'Cada cuenta de pago necesita el número.',
            'cuentas_pago.*.titular.required'       => 'Cada cuenta de pago necesita el titular.',
            'contactos_informes.*.nombre.required'  => 'Cada contacto necesita el nombre.',
            'contactos_informes.*.celular.required' => 'Cada contacto necesita el celular.',
            'contactos_informes.*.celular.regex'    => 'El celular de un contacto solo puede tener números.',
        ];
    }



    // "210.000" o "$210.000" -> "210000"
    protected function limpiarValor($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        return preg_replace('/[^\d]/', '', (string) $valor);
    }



    //  Las filas que el usuario dejó vacías en el formulario se
    // descartan, para no exigir datos de una fila que no quiso usar.
    protected function filasLlenas($filas, array $campos): array
    {
        return collect($filas ?? [])
            ->filter(fn ($fila) => collect($campos)->contains(fn ($campo) => filled($fila[$campo] ?? null)))
            ->values()
            ->all();
    }
}
