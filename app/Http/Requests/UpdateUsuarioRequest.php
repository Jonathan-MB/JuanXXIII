<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends StoreUsuarioRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        $reglas = parent::rules();

        $reglas['usuario']    = ['required', 'string', 'max:45', 'regex:/^[\pL\pN ._-]+$/u', Rule::unique('usuarios', 'usuario')->ignore($this->route('usuario')->id)];

        // Al editar, la contraseña solo cambia si se escribe una nueva
        $reglas['contrasena'] = ['nullable', 'string', 'min:8', 'confirmed'];

        // PATCH solo trae "activo" (activar o desactivar desde la lista)
        if ($this->isMethod('patch')) {
            return ['activo' => ['required', 'boolean']];
        }

        return $reglas;
    }



    protected  function prepareForValidation(): void
    {
        if ($this->isMethod('put')) {
            parent::prepareForValidation();
            return;
        }

        //  En PATCH solo se mete al merge lo que realmente vino
        if ($this->has('activo')) {
            $this->merge(['activo' => $this->boolean('activo')]);
        }
    }
}
