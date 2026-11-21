<?php

namespace App\Http\Requests;

use App\Models\Mesa;
use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

//  La fila de cada invitado en Llegada: lo que más se corrige en la puerta
// (nombres, apellidos, documento, celular, fecha de nacimiento, talla,
// ciudad, barrio y los dos contactos de emergencia) y su mesa y silla.
// Lo demás está en «Más datos».
class CorregirDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'nombres'          => ['required', 'string', 'max:60'],
            'apellidos'        => ['required', 'string', 'max:60'],
            'cedula'           => ['nullable', 'string', 'min:4', 'max:20', 'regex:/^[0-9A-Za-z]+$/', Rule::unique('personas', 'cedula')->ignore($this->route('inscripcion')->persona_id)],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'celular'          => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'],
            'ciudad'           => ['nullable', 'string', 'max:60'],
            'barrio'           => ['nullable', 'string', 'max:60'],
            'talla_camiseta'   => ['nullable', Rule::in(Persona::TALLAS)],

            //  Pueden quedar a medias: muchos llegaron así del formulario antiguo
            'contactos'              => ['required', 'array', 'size:2'],
            'contactos.*.nombre'     => ['nullable', 'string', 'max:100'],
            'contactos.*.parentesco' => ['nullable', 'string', 'max:45'],
            'contactos.*.celular'    => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'],

            //  Las dos casillas vacías quitan el puesto
            'mesa'  => ['nullable', 'required_with:silla', 'integer', 'between:1,99'],
            'silla' => ['nullable', 'required_with:mesa', 'integer', 'between:1,' . Mesa::MAXIMO_SILLAS],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'nombres'          => trim((string) $this->nombres),
            'apellidos'        => trim((string) $this->apellidos),
            'cedula'           => Persona::normalizarCedula($this->cedula) ?: null,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'celular'          => (string) Persona::normalizarTelefono($this->celular),
            'ciudad'           => trim((string) $this->ciudad),
            'barrio'           => trim((string) $this->barrio),
            'talla_camiseta'   => $this->tallaCamiseta ?: null,
            'contactos'        => collect($this->contactos ?? [])->map(fn ($contacto) => [
                'nombre'     => trim((string) ($contacto['nombre'] ?? '')),
                'parentesco' => trim((string) ($contacto['parentesco'] ?? '')),
                'celular'    => (string) Persona::normalizarTelefono($contacto['celular'] ?? null),
            ])->values()->all(),
        ]);
    }



    public function messages(): array
    {
        return [
            'nombres.required'          => 'Escribe los nombres.',
            'apellidos.required'        => 'Escribe los apellidos.',
            'cedula.required'           => 'Escribe el documento.',
            'cedula.min'                => 'El documento parece incompleto.',
            'cedula.regex'              => 'El documento solo puede tener números (o letras y números si es un pasaporte).',
            'cedula.unique'             => 'Ese documento ya pertenece a otra persona registrada.',
            'fecha_nacimiento.required' => 'Escribe la fecha de nacimiento.',
            'fecha_nacimiento.date'     => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before'   => 'La fecha de nacimiento no es válida.',
            'celular.min'               => 'El celular parece incompleto.',
            'celular.regex'             => 'El celular solo puede tener números.',
            'talla_camiseta.required'   => 'Elige la talla.',
            'talla_camiseta.in'         => 'Esa talla no existe.',
            'contactos.size'            => 'Faltan los dos contactos de emergencia.',
            'contactos.*.celular.min'   => 'El celular del contacto parece incompleto.',
            'contactos.*.celular.regex' => 'El celular del contacto solo puede tener números.',
            'mesa.required_with'        => 'Escribe también la mesa.',
            'silla.required_with'       => 'Escribe también la silla.',
            'mesa.between'              => 'La mesa va de 1 a 99.',
            'silla.between'             => 'La silla va de 1 a ' . Mesa::MAXIMO_SILLAS . '.',
        ];
    }
}
