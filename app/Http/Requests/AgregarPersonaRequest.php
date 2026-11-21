<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

//  Secretaría · Agregar persona: para quien llega al retiro sin haberse
// inscrito. Solo se piden nombres, apellidos y si es hombre o mujer; lo
// demás puede quedar pendiente y se completa en la verificación de datos.
// La inscripción pública NO usa estas reglas: allá se pide todo.
class AgregarPersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'sexo'             => ['required', Rule::in(['Masculino', 'Femenino'])],
            'nombres'          => ['required', 'string', 'max:60'],
            'apellidos'        => ['required', 'string', 'max:60'],
            'cedula'           => ['nullable', 'string', 'min:4', 'max:20', 'regex:/^[0-9A-Za-z]+$/'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'celular'          => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'],
            'ciudad'           => ['nullable', 'string', 'max:60'],
            'barrio'           => ['nullable', 'string', 'max:60'],
            'talla_camiseta'   => ['nullable', Rule::in(Persona::TALLAS)],
        ];
    }



    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombres'          => trim((string) $this->nombres),
            'apellidos'        => trim((string) $this->apellidos),
            'cedula'           => Persona::normalizarCedula($this->cedula) ?: null,
            'fecha_nacimiento' => $this->fechaNacimiento ?: null,
            'celular'          => Persona::normalizarTelefono($this->celular),
            'ciudad'           => trim((string) $this->ciudad) ?: null,
            'barrio'           => trim((string) $this->barrio) ?: null,
            'talla_camiseta'   => $this->tallaCamiseta ?: null,
        ]);
    }



    public function messages(): array
    {
        return [
            'nombres.required'        => 'Escribe los nombres.',
            'apellidos.required'      => 'Escribe los apellidos.',
            'cedula.min'              => 'El documento parece incompleto.',
            'cedula.regex'            => 'El documento solo puede tener números (o letras y números si es un pasaporte).',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no es válida.',
            'celular.min'             => 'El celular parece incompleto.',
            'celular.regex'           => 'El celular solo puede tener números.',
        ];
    }
}
