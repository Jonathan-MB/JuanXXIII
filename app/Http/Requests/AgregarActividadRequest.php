<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgregarActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'sexo'         => ['required', 'in:hombres,mujeres'],
            'dia_relativo' => ['required', 'integer', 'between:0,2'],
            'hora_inicio'  => ['required', 'date_format:H:i'],
            'hora_fin'     => ['nullable', 'date_format:H:i'],
            'nombre'       => ['required', 'string', 'max:100'],
            'es_charla'    => ['boolean'],
            'charlista'    => ['nullable', 'string', 'max:100'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'dia_relativo' => $this->diaRelativo,
            'hora_inicio'  => substr((string) $this->horaInicio, 0, 5),
            'hora_fin'     => substr((string) $this->horaFin, 0, 5) ?: null,
            'nombre'       => trim((string) $this->nombre),
            'es_charla'    => $this->boolean('esCharla'),
        ]);
    }



    public function messages(): array
    {
        return [
            'hora_inicio.required'    => 'Escribe la hora de inicio.',
            'hora_inicio.date_format' => 'La hora de inicio no es válida.',
            'hora_fin.date_format'    => 'La hora de fin no es válida.',
            'nombre.required'         => 'Escribe el nombre de la actividad.',
        ];
    }
}
