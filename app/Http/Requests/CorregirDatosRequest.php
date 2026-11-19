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



    //  Los datos de la hoja de la mesa. El padrino vacío quiere decir que
    // no tiene padrino.
    public function rules(): array
    {
        return [
            'nombres'          => ['required', 'string', 'max:60'],
            'apellidos'        => ['required', 'string', 'max:60'],
            'cedula'           => ['required', 'string', 'min:4', 'max:20', 'regex:/^[0-9]+$/', Rule::unique('personas', 'cedula')->ignore($this->route('inscripcion')->persona_id)],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'celular'          => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'],
            'ciudad'           => ['required', 'string', 'max:60'],
            'barrio'           => ['required', 'string', 'max:60'],
            'padrino'          => ['nullable', 'string', 'max:100'],
            'padrino_telefono' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'],

            //  Los dos contactos de emergencia de la hoja de la mesa
            'contactos'              => ['required', 'array', 'size:2'],
            'contactos.*.nombre'     => ['required', 'string', 'max:100'],
            'contactos.*.parentesco' => ['required', 'string', 'max:45'],
            'contactos.*.celular'    => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'nombres'          => trim((string) $this->nombres),
            'apellidos'        => trim((string) $this->apellidos),
            'cedula'           => Persona::normalizarCedula($this->cedula),
            'fecha_nacimiento' => $this->fechaNacimiento,
            'celular'          => Persona::normalizarTelefono($this->celular),
            'ciudad'           => trim((string) $this->ciudad),
            'barrio'           => trim((string) $this->barrio),
            'padrino'          => trim((string) $this->padrino) ?: null,
            'padrino_telefono' => Persona::normalizarTelefono($this->padrinoTelefono),
            'contactos'        => collect($this->contactos ?? [])->map(fn ($contacto) => [
                'nombre'     => trim((string) ($contacto['nombre'] ?? '')),
                'parentesco' => trim((string) ($contacto['parentesco'] ?? '')),
                'celular'    => Persona::normalizarTelefono($contacto['celular'] ?? null),
            ])->values()->all(),
        ]);
    }



    public function messages(): array
    {
        return [
            'nombres.required'          => 'Escribe los nombres.',
            'apellidos.required'        => 'Escribe los apellidos.',
            'cedula.required'           => 'Escribe la cédula.',
            'cedula.regex'              => 'La cédula solo puede tener números.',
            'cedula.unique'             => 'Esa cédula ya pertenece a otra persona registrada.',
            'fecha_nacimiento.required' => 'Escribe la fecha de nacimiento.',
            'fecha_nacimiento.before'   => 'La fecha de nacimiento no es válida.',
            'celular.required'          => 'Escribe el celular.',
            'celular.min'               => 'El celular parece incompleto.',
            'celular.regex'             => 'El celular solo puede tener números.',
            'ciudad.required'           => 'Escribe la ciudad.',
            'barrio.required'           => 'Escribe el barrio.',
            'padrino_telefono.min'      => 'El teléfono del padrino parece incompleto.',
            'padrino_telefono.regex'    => 'El teléfono del padrino solo puede tener números.',
            'contactos.size'            => 'Faltan los dos contactos de emergencia.',
            'contactos.*.nombre.required'     => 'Escribe el nombre de cada contacto de emergencia.',
            'contactos.*.parentesco.required' => 'Escribe el parentesco de cada contacto de emergencia.',
            'contactos.*.celular.required'    => 'Escribe el celular de cada contacto de emergencia.',
            'contactos.*.celular.min'         => 'El celular de un contacto de emergencia parece incompleto.',
            'contactos.*.celular.regex'       => 'El celular de un contacto de emergencia solo puede tener números.',
        ];
    }
}
