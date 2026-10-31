<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarRectoradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'sexo'              => ['required', 'in:Masculino,Femenino'],
            'cargos'            => ['array'],
            'cargos.*.cargo'    => ['required', 'string', 'max:40'],
            'cargos.*.nombre'   => ['required', 'string', 'max:100'],
            'cargos.*.ciudad'   => ['nullable', 'string', 'max:60'],
            'cargos.*.telefono' => ['nullable', 'string', 'max:20'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        //  Los cargos sin nombre se descartan: el CR es opcional
        $this->merge([
            'cargos' => collect($this->cargos ?? [])
                ->filter(fn ($fila) => filled($fila['nombre'] ?? null))
                ->values()
                ->all(),
        ]);
    }



    public function messages(): array
    {
        return [
            'cargos.*.nombre.max'   => 'Un nombre del rectorado es demasiado largo.',
            'cargos.*.telefono.max' => 'Un teléfono del rectorado es demasiado largo.',
        ];
    }
}
