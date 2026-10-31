<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'nombre'     => ['required', 'string', 'max:60'],
            'usuario'    => ['required', 'string', 'max:45', 'regex:/^[\pL\pN ._-]+$/u', 'unique:usuarios,usuario'],
            'correo'     => ['nullable', 'email', 'max:100'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
            'rol_id'     => ['required', 'integer', 'exists:rols,id'],
            'equipo_id'  => ['nullable', 'integer', 'exists:equipos,id'],
            'activo'     => ['boolean'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'usuario'                 => trim((string) $this->usuario),
            'contrasena'              => $this->contrasena,
            'contrasena_confirmation' => $this->contrasenaConfirmacion,
            'rol_id'                  => $this->rolId,
            'equipo_id'               => $this->equipoId ?: null,
            'activo'                  => $this->boolean('activo'),
        ]);
    }



    public function messages(): array
    {
        return [
            'nombre.required'      => 'Escribe el nombre de la persona.',
            'usuario.required'     => 'Escribe el nombre de usuario con el que va a entrar.',
            'usuario.unique'       => 'Ese nombre de usuario ya existe. Elige otro.',
            'usuario.regex'        => 'El usuario solo puede tener letras, números, espacios, punto, guion y guion bajo.',
            'correo.email'         => 'El correo no es válido.',
            'contrasena.required'  => 'Escribe una contraseña.',
            'contrasena.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las dos contraseñas no coinciden.',
            'rol_id.required'      => 'Elige el rol de la cuenta.',
            'rol_id.exists'        => 'El rol elegido no existe.',
            'equipo_id.exists'     => 'El equipo elegido no existe.',
        ];
    }
}
