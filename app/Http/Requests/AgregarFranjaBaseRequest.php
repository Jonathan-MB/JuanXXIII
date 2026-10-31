<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgregarFranjaBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  Las franjas empiezan en punto o y media, y no se repiten en el
    // mismo día.
    public function rules(): array
    {
        return [
            'dia_relativo' => ['required', 'integer', 'between:0,2'],
            'hora_inicio'  => [
                'required',
                'regex:/^([01]\d|2[0-3]):(00|30):00$/',
                Rule::unique('santisimo_bases')->where('dia_relativo', $this->dia_relativo),
            ],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'dia_relativo' => $this->diaRelativo,
            'hora_inicio'  => substr((string) $this->horaInicio, 0, 5) . ':00',
        ]);
    }



    public function messages(): array
    {
        return [
            'dia_relativo.between' => 'El día debe ser viernes, sábado o domingo.',
            'hora_inicio.regex'    => 'La hora debe ser en punto o y media (por ejemplo 4:00 o 4:30).',
            'hora_inicio.unique'   => 'Esa franja ya existe en el horario base.',
        ];
    }
}
