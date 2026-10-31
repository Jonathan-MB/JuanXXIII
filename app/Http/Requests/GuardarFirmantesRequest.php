<?php

namespace App\Http\Requests;

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
            'firmantes.*.celular' => ['nullable', 'string', 'max:20'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        // Las filas que quedaron vacías no se guardan
        $this->merge([
            'firmantes' => collect($this->firmantes ?? [])
                ->filter(fn ($fila) => filled($fila['nombre'] ?? null) || filled($fila['cargo'] ?? null))
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
        ];
    }
}
