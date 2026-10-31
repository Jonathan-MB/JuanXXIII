<?php

namespace App\Http\Requests;

use App\Models\MovimientoInventario;
use Illuminate\Foundation\Http\FormRequest;

class MovimientoInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'tipo'     => ['required', 'in:' . implode(',', [MovimientoInventario::ENTRADA, MovimientoInventario::SALIDA, MovimientoInventario::AJUSTE])],
            // El ajuste puede dejar el artículo en 0; entrada y salida mueven al menos 1
            'cantidad' => ['required', 'integer', $this->tipo === MovimientoInventario::AJUSTE ? 'min:0' : 'min:1', 'max:100000'],
            'nota'     => ['nullable', 'string', 'max:255'],
        ];
    }



    public function messages(): array
    {
        return [
            'cantidad.required' => 'Escribe la cantidad.',
            'cantidad.min'      => 'La cantidad debe ser mayor que cero.',
            'tipo.in'           => 'Ese movimiento no es válido.',
        ];
    }
}
