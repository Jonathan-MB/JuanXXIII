<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateInscritoRequest extends StoreInscripcionRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  Las mismas reglas de la inscripción pública, menos lo que solo
    // tiene sentido al inscribirse (autorizaciones y comprobante).
    public function rules(): array
    {
        $reglas = collect(parent::rules())
            ->except(['comprobante', 'acepta_datos', 'declara_requisitos'])
            ->all();

        $personaId        = $this->route('inscripcion')->persona_id;
        $reglas['cedula'] = ['required', 'string', 'min:4', 'max:20', 'regex:/^[0-9A-Za-z]+$/', Rule::unique('personas', 'cedula')->ignore($personaId)];

        //  Secretaría corrige datos que llegaron incompletos (el formulario
        // antiguo no los pedía): se puede guardar sin correo, sin EPS y con
        // contactos a medias, para no tener que inventar nada. La cédula
        // admite letras por los pasaportes.
        $reglas['correo']                            = ['nullable', 'email', 'max:100'];
        $reglas['eps']                               = ['nullable', 'string', 'max:60'];
        $reglas['contactos_emergencia']              = ['nullable', 'array', 'max:2'];
        $reglas['contactos_emergencia.*.nombre']     = ['nullable', 'string', 'max:100'];
        $reglas['contactos_emergencia.*.celular']    = ['nullable', 'string', 'min:7', 'max:20', 'regex:/^[0-9]+$/'];
        $reglas['contactos_emergencia.*.parentesco'] = ['nullable', 'string', 'max:45'];

        return $reglas;
    }



    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'cedula.unique' => 'Esa cédula ya pertenece a otra persona registrada.',
        ]);
    }
}
