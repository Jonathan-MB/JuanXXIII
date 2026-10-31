<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarPerseveranciasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'perseverancias'             => ['array'],
            'perseverancias.*.tipo'      => ['required', 'string', 'max:40'],
            'perseverancias.*.dia'       => ['required', 'string', 'max:30'],
            'perseverancias.*.hora'      => ['required', 'string', 'max:20'],
            'perseverancias.*.parroquia' => ['required', 'string', 'max:120'],
            'perseverancias.*.direccion' => ['nullable', 'string', 'max:120'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        // Las filas que quedaron vacías no se guardan
        $this->merge([
            'perseverancias' => collect($this->perseverancias ?? [])
                ->filter(fn ($fila) => filled($fila['parroquia'] ?? null) || filled($fila['dia'] ?? null))
                ->values()
                ->all(),
        ]);
    }



    public function messages(): array
    {
        return [
            'perseverancias.*.tipo.required'      => 'Cada perseverancia necesita el tipo.',
            'perseverancias.*.dia.required'       => 'Cada perseverancia necesita el día.',
            'perseverancias.*.hora.required'      => 'Cada perseverancia necesita la hora.',
            'perseverancias.*.parroquia.required' => 'Cada perseverancia necesita la parroquia o el lugar.',
        ];
    }
}
