<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateArticuloRequest extends StoreArticuloRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  La cantidad no se edita aquí: cambia solo con entradas, salidas
    // o un ajuste, para que siempre quede registrado el movimiento.
    public function rules(): array
    {
        $articulo = $this->route('articulo');

        return [
            'nombre'      => ['required', 'string', 'max:80', Rule::unique('articulos', 'nombre')->where('equipo_id', $articulo->equipo_id)->ignore($articulo->id)],
            'unidad'      => ['required', 'string', 'max:20'],
            'por_comprar' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'estado'      => ['nullable', 'string', 'max:60'],
        ];
    }
}
