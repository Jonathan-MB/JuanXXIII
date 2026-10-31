<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRetiroRequest extends StoreRetiroRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        $retiroId = $this->route('retiro')->id;
        $reglas   = parent::rules();

        $reglas['numero_hombres'] = ['required', 'integer', 'min:1', Rule::unique('retiros', 'numero_hombres')->ignore($retiroId)];
        $reglas['numero_mujeres'] = ['required', 'integer', 'min:1', Rule::unique('retiros', 'numero_mujeres')->ignore($retiroId)];

        // PATCH solo valida lo que vino (abrir o cerrar inscripciones)
        if ($this->isMethod('patch')) {
            return collect($reglas)
                ->map(fn ($regla) => array_merge(['sometimes'], (array) $regla))
                ->all();
        }

        return $reglas;
    }



    protected  function prepareForValidation(): void
    {
        if ($this->isMethod('put')) {
            parent::prepareForValidation();
            return;
        }

        //  En PATCH solo se mete al merge lo que realmente vino,
        // para no llenar de nulos los campos que no se tocaron.
        if ($this->has('inscripcionesAbiertas')) {
            $this->merge(['inscripciones_abiertas' => $this->boolean('inscripcionesAbiertas')]);
        }
    }
}
