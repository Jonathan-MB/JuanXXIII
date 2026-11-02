<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarEquiposSantisimoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  Por cada equipo: cuántas personas tiene (aproximado) y si entra en
    // el reparto. Una casilla sin marcar no llega, por eso es nullable.
    public function rules(): array
    {
        return [
            'equipos'             => ['required', 'array'],
            'equipos.*.personas'  => ['required', 'integer', 'between:0,500'],
            'equipos.*.participa' => ['nullable', 'boolean'],
        ];
    }



    public function messages(): array
    {
        return [
            'equipos.required'            => 'No llegaron equipos para guardar.',
            'equipos.*.personas.required' => 'Escribe cuántas personas tiene cada equipo (0 si no se sabe).',
            'equipos.*.personas.integer'  => 'El número de personas debe ser un número entero.',
            'equipos.*.personas.between'  => 'El número de personas debe estar entre 0 y 500.',
        ];
    }
}
