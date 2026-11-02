<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RepartirSantisimoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  Se reparte en la base o en la copia del retiro actual. El turno es
    // cuántas franjas seguidas le tocan a un equipo antes de cambiar.
    public function rules(): array
    {
        return [
            'destino' => ['required', Rule::in(['base', 'retiro'])],
            'turno'   => ['required', 'integer', 'between:1,4'],
        ];
    }



    public function messages(): array
    {
        return [
            'destino.in'    => 'Elige si se reparte la base o el horario del retiro.',
            'turno.between' => 'El turno debe ser de 30 minutos a 2 horas.',
        ];
    }
}
