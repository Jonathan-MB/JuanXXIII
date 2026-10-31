<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorregirDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'nombres'   => ['required', 'string', 'max:60'],
            'apellidos' => ['required', 'string', 'max:60'],
            'cedula'    => ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Z0-9]+$/', Rule::unique('personas', 'cedula')->ignore($this->route('inscripcion')->persona_id)],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'nombres'   => trim((string) $this->nombres),
            'apellidos' => trim((string) $this->apellidos),
            'cedula'    => Persona::normalizarCedula($this->cedula),
        ]);
    }



    public function messages(): array
    {
        return [
            'nombres.required'   => 'Escribe los nombres.',
            'apellidos.required' => 'Escribe los apellidos.',
            'cedula.required'    => 'Escribe la cédula.',
            'cedula.regex'       => 'La cédula solo puede tener números y letras.',
            'cedula.unique'      => 'Esa cédula ya pertenece a otra persona registrada.',
        ];
    }
}
