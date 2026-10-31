<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TareaCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'dia_relativo' => ['required', 'integer', 'between:-14,2'],
            'hora'         => ['required', 'date_format:H:i'],
            'descripcion'  => ['required', 'string', 'max:200'],
            'responsable'  => ['nullable', 'string', 'max:60'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'dia_relativo' => $this->diaRelativo,
            'hora'         => substr((string) $this->hora, 0, 5),
        ]);
    }



    public function messages(): array
    {
        return [
            'dia_relativo.between' => 'El día va desde 14 días antes hasta el domingo.',
            'hora.date_format'     => 'La hora no es válida.',
            'descripcion.required' => 'Escribe qué hay que hacer.',
        ];
    }
}
