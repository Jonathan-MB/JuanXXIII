<?php

namespace App\Http\Requests;

use App\Models\Equipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarFranjasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  Sirve para la base y para la copia del retiro. En cada franja,
    // "cubre" es vacío (nadie), "todos" o el id de un equipo.
    public function rules(): array
    {
        $opciones = Equipo::pluck('id')->map(fn ($id) => (string) $id)->push('todos')->all();

        return [
            'franjas'             => ['required', 'array'],
            'franjas.*.cubre'     => ['nullable', 'string', Rule::in($opciones)],
            'franjas.*.actividad' => ['nullable', 'string', 'max:60'],
        ];
    }



    public function messages(): array
    {
        return [
            'franjas.required'        => 'No llegaron franjas para guardar.',
            'franjas.*.cubre.in'      => 'Uno de los equipos elegidos ya no existe. Recarga la página.',
            'franjas.*.actividad.max' => 'La actividad puede tener máximo 60 letras.',
        ];
    }
}
