<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportarRetiristasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  El sexo, el número del retiro y el año son para cuando el archivo
    // no los trae en una columna (por ejemplo, un Excel por retiro).
    public function rules(): array
    {
        return [
            'archivo'       => ['required', 'file', 'extensions:xlsx,csv,txt', 'max:10240'],
            'sexo'          => ['nullable', Rule::in(['Masculino', 'Femenino'])],
            'numero_retiro' => ['nullable', 'integer', 'between:1,999'],
            'anio'          => ['nullable', 'integer', 'between:1960,2100'],
            'hoja'          => ['nullable', 'string', 'max:60'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'numero_retiro' => $this->numeroRetiro ?: null,
            'anio'          => $this->anio ?: null,
            'sexo'          => $this->sexo ?: null,
            'hoja'          => trim((string) $this->hoja) ?: null,
        ]);
    }



    public function messages(): array
    {
        return [
            'archivo.required'      => 'Elige el archivo.',
            'archivo.extensions'    => 'El archivo debe ser de Excel (.xlsx) o CSV.',
            'archivo.max'           => 'El archivo puede pesar máximo 10 MB.',
            'numero_retiro.between' => 'El número del retiro debe estar entre 1 y 999.',
            'anio.between'          => 'Revisa el año.',
        ];
    }
}
