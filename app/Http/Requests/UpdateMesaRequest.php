<?php

namespace App\Http\Requests;

use App\Models\Mesa;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'nombre'              => ['nullable', 'string', 'max:60'],
            'cantidad_sillas'     => ['required', 'integer', 'between:1,' . Mesa::MAXIMO_SILLAS],
            'catequista_titular'  => ['nullable', 'string', 'max:100'],
            'catequista_auxiliar' => ['nullable', 'string', 'max:100'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'cantidad_sillas'     => $this->cantidadSillas,
            'catequista_titular'  => $this->catequistaTitular,
            'catequista_auxiliar' => $this->catequistaAuxiliar,
        ]);
    }



    public function messages(): array
    {
        return [
            'cantidad_sillas.between' => 'Cada mesa puede tener entre 1 y ' . Mesa::MAXIMO_SILLAS . ' sillas.',
        ];
    }
}
