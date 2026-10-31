<?php

namespace App\Http\Requests;

use App\Models\Mesa;
use App\Models\Retiro;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'sexo'                => ['required', 'in:Masculino,Femenino'],
            'numero'              => ['required', 'integer', 'between:1,99', Rule::unique('mesas', 'numero')->where('retiro_id', Retiro::actual()?->id)->where('sexo', $this->sexo)],
            'cantidad_sillas'     => ['required', 'integer', 'between:1,' . Mesa::MAXIMO_SILLAS],
            'nombre'              => ['nullable', 'string', 'max:60'],
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
            'numero.required'         => 'Escribe el número de la mesa.',
            'numero.unique'           => 'Esa mesa ya existe en este grupo.',
            'cantidad_sillas.between' => 'Cada mesa puede tener entre 1 y ' . Mesa::MAXIMO_SILLAS . ' sillas.',
        ];
    }
}
