<?php

namespace App\Http\Requests;

use App\Models\Archivo;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'titulo'      => ['required', 'string', 'max:150'],
            'numero'      => ['nullable', 'integer', 'between:0,999'],
            'categoria'   => ['required', 'in:' . implode(',', array_keys(Archivo::CATEGORIAS))],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }



    public function messages(): array
    {
        return [
            'titulo.required' => 'Escribe el título del archivo.',
            'numero.between'  => 'El número va de 0 a 999.',
        ];
    }
}
