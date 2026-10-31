<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'sexo'                                 => ['required', 'in:Masculino,Femenino'],
            'nombres'                              => ['required', 'string', 'max:60'],
            'apellidos'                            => ['required', 'string', 'max:60'],
            'cedula'                               => ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            'fecha_nacimiento'                     => ['required', 'date', 'before:today'],
            'correo'                               => ['required', 'email', 'max:100'],
            'celular'                              => ['required', 'string', 'min:7', 'max:20'],
            'direccion'                            => ['required', 'string', 'max:100'],
            'barrio'                               => ['required', 'string', 'max:60'],
            'ciudad'                               => ['required', 'string', 'max:60'],

            'bautismo'                             => ['boolean'],
            'primera_comunion'                     => ['boolean'],
            'confirmacion'                         => ['boolean'],
            'matrimonio'                           => ['boolean'],
            'toma_medicamentos'                    => ['required', 'boolean'],
            'medicamentos'                         => ['required_if:toma_medicamentos,1', 'nullable', 'string', 'max:255'],
            'eps'                                  => ['required', 'string', 'max:60'],
            'talla_camiseta'                       => ['required', Rule::in(Persona::TALLAS)],

            'contactos_emergencia'                 => ['required', 'array', 'size:2'],
            'contactos_emergencia.*.nombre'        => ['required', 'string', 'max:100'],
            'contactos_emergencia.*.celular'       => ['required', 'string', 'min:7', 'max:20'],
            'contactos_emergencia.*.telefono_fijo' => ['nullable', 'string', 'max:20'],
            'contactos_emergencia.*.correo'        => ['nullable', 'email', 'max:100'],
            'contactos_emergencia.*.parentesco'    => ['required', 'string', 'max:45'],

            'forma_pago'                           => ['required', 'string', 'max:120'],
            'comprobante'                          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'tiene_padrino'                        => ['required', 'boolean'],
            'padrino_nombre'                       => ['required_if:tiene_padrino,1', 'nullable', 'string', 'max:100'],
            'padrino_telefono'                     => ['nullable', 'string', 'max:20'],
            'padrino_presente'                     => ['nullable', 'string', 'max:60'],
            'padrino_parroquia'                    => ['nullable', 'string', 'max:150'],
            'acepta_datos'                         => ['accepted'],
            'declara_requisitos'                   => ['accepted'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        //  "Otro" en la forma de pago trae su propio texto
        $formaPago = $this->formaPago === 'Otro' ? trim('Otro: ' . $this->formaPagoOtro) : $this->formaPago;

        $this->merge([
            'cedula'               => Persona::normalizarCedula($this->cedula),
            'fecha_nacimiento'     => $this->fechaNacimiento,
            'primera_comunion'     => $this->boolean('primeraComunion'),
            'bautismo'             => $this->boolean('bautismo'),
            'confirmacion'         => $this->boolean('confirmacion'),
            'matrimonio'           => $this->boolean('matrimonio'),
            'toma_medicamentos'    => $this->tomaMedicamentos,
            'medicamentos'         => $this->tomaMedicamentos === '1' ? $this->medicamentos : null,
            'talla_camiseta'       => $this->tallaCamiseta,
            'contactos_emergencia' => collect($this->contactosEmergencia ?? [])
                ->map(fn ($contacto) => [
                    'nombre'        => $contacto['nombre'] ?? null,
                    'celular'       => $contacto['celular'] ?? null,
                    'telefono_fijo' => $contacto['telefonoFijo'] ?? null,
                    'correo'        => $contacto['correo'] ?? null,
                    'parentesco'    => $contacto['parentesco'] ?? null,
                ])
                ->values()
                ->all(),
            'forma_pago'           => $formaPago,
            'tiene_padrino'        => $this->tienePadrino,
            'padrino_nombre'       => $this->padrinoNombre,
            'padrino_telefono'     => $this->padrinoTelefono,
            'padrino_presente'     => $this->padrinoPresente,
            'padrino_parroquia'    => $this->padrinoParroquia,
            'acepta_datos'         => $this->aceptaDatos,
            'declara_requisitos'   => $this->declaraRequisitos,
        ]);
    }



    public function messages(): array
    {
        return [
            'sexo.required'                              => 'Elige si eres hombre o mujer.',
            'nombres.required'                           => 'Escribe tus nombres.',
            'apellidos.required'                         => 'Escribe tus apellidos.',
            'cedula.required'                            => 'Escribe tu número de cédula.',
            'cedula.regex'                               => 'La cédula solo puede tener números y letras.',
            'cedula.min'                                 => 'La cédula parece incompleta.',
            'fecha_nacimiento.required'                  => 'Escribe tu fecha de nacimiento.',
            'fecha_nacimiento.before'                    => 'La fecha de nacimiento no es válida.',
            'correo.required'                            => 'Escribe tu correo electrónico.',
            'correo.email'                               => 'El correo electrónico no es válido.',
            'celular.required'                           => 'Escribe tu número de celular.',
            'celular.min'                                => 'El celular parece incompleto.',
            'direccion.required'                         => 'Escribe tu dirección.',
            'barrio.required'                            => 'Escribe tu barrio.',
            'ciudad.required'                            => 'Escribe tu ciudad.',
            'toma_medicamentos.required'                 => 'Dinos si tomas medicamentos.',
            'medicamentos.required_if'                   => 'Escribe qué medicamentos tomas y en qué horario.',
            'eps.required'                               => 'Escribe tu EPS o entidad de salud.',
            'talla_camiseta.required'                    => 'Elige tu talla de camiseta.',
            'contactos_emergencia.size'                  => 'Necesitamos dos contactos de emergencia.',
            'contactos_emergencia.*.nombre.required'     => 'Escribe el nombre de cada contacto de emergencia.',
            'contactos_emergencia.*.celular.required'    => 'Escribe el celular de cada contacto de emergencia.',
            'contactos_emergencia.*.celular.min'         => 'El celular de un contacto de emergencia parece incompleto.',
            'contactos_emergencia.*.correo.email'        => 'El correo de un contacto de emergencia no es válido.',
            'contactos_emergencia.*.parentesco.required' => 'Escribe el parentesco de cada contacto de emergencia.',
            'forma_pago.required'                        => 'Elige la forma de pago.',
            'comprobante.mimes'                          => 'El comprobante debe ser una foto (JPG, PNG, WEBP) o un PDF.',
            'comprobante.max'                            => 'El comprobante pesa más de 5 MB.',
            'tiene_padrino.required'                     => 'Dinos si tienes un retirista que te apadrine.',
            'padrino_nombre.required_if'                 => 'Escribe el nombre de tu padrino.',
            'acepta_datos.accepted'                      => 'Debes autorizar el tratamiento de tus datos (Ley 1581 de 2012).',
            'declara_requisitos.accepted'                => 'Debes confirmar que cumples los requisitos del retiro.',
        ];
    }
}
