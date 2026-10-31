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
        $reglas['cedula'] = ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Z0-9]+$/', Rule::unique('personas', 'cedula')->ignore($personaId)];

        return $reglas;
    }



    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'cedula.unique' => 'Esa cédula ya pertenece a otra persona registrada.',
        ]);
    }
}
