<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:80', Rule::unique('articulos', 'nombre')->where('equipo_id', $this->route('equipo')->id)],
            'unidad'      => ['required', 'string', 'max:20'],
            'cantidad'    => ['required', 'integer', 'min:0', 'max:100000'],
            'por_comprar' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'estado'      => ['nullable', 'string', 'max:60'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'nombre'      => trim((string) $this->nombre),
            'por_comprar' => $this->porComprar ?: 0,
        ]);
    }



    public function messages(): array
    {
        return [
            'nombre.required'   => 'Escribe el nombre del artículo.',
            'nombre.unique'     => 'Este equipo ya tiene un artículo con ese nombre.',
            'unidad.required'   => 'Escribe la unidad (unidades, paquetes, cajas…).',
            'cantidad.required' => 'Escribe cuántos hay.',
            'cantidad.min'      => 'La cantidad no puede ser negativa.',
        ];
    }
}
