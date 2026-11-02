<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;

class GuardarFirmantesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'firmantes'           => ['array', 'max:3'],
            'firmantes.*.nombre'  => ['required', 'string', 'max:100'],
            'firmantes.*.cargo'   => ['required', 'string', 'max:100'],
            'firmantes.*.celular' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        // Las filas que quedaron vacías no se guardan
        $this->merge([
            'firmantes' => collect($this->firmantes ?? [])
                ->filter(fn ($fila) => filled($fila['nombre'] ?? null) || filled($fila['cargo'] ?? null))
                ->map(fn ($fila) => array_merge($fila, ['celular' => Persona::normalizarTelefono($fila['celular'] ?? null)]))
                ->values()
                ->all(),
        ]);
    }



    public function messages(): array
    {
        return [
            'firmantes.max'               => 'La carta tiene espacio para máximo tres firmas.',
            'firmantes.*.nombre.required' => 'Cada firma necesita el nombre.',
            'firmantes.*.cargo.required'  => 'Cada firma necesita el cargo.',
            'firmantes.*.celular.regex'   => 'El celular de una firma solo puede tener números.',
        ];
    }
}
