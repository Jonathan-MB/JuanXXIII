<?php

namespace App\Http\Requests;

use App\Models\Mesa;
use App\Models\Persona;
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
            'titular_celular'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'titular_ciudad'      => ['nullable', 'string', 'max:60'],
            'titular_barrio'      => ['nullable', 'string', 'max:60'],
            'catequista_auxiliar' => ['nullable', 'string', 'max:100'],
            'auxiliar_celular'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'auxiliar_ciudad'     => ['nullable', 'string', 'max:60'],
            'auxiliar_barrio'     => ['nullable', 'string', 'max:60'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'cantidad_sillas'     => $this->cantidadSillas,
            'catequista_titular'  => $this->catequistaTitular,
            'titular_celular'     => Persona::normalizarTelefono($this->titularCelular),
            'titular_ciudad'      => $this->titularCiudad,
            'titular_barrio'      => $this->titularBarrio,
            'catequista_auxiliar' => $this->catequistaAuxiliar,
            'auxiliar_celular'    => Persona::normalizarTelefono($this->auxiliarCelular),
            'auxiliar_ciudad'     => $this->auxiliarCiudad,
            'auxiliar_barrio'     => $this->auxiliarBarrio,
        ]);
    }



    public function messages(): array
    {
        return [
            'cantidad_sillas.between' => 'Cada mesa puede tener entre 1 y ' . Mesa::MAXIMO_SILLAS . ' sillas.',
            'titular_celular.regex'   => 'El celular del CT solo puede tener números.',
            'auxiliar_celular.regex'  => 'El celular del CA solo puede tener números.',
        ];
    }
}
