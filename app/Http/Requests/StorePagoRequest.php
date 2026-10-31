<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'valor'       => ['required', 'numeric', 'min:1'],
            'fecha'       => ['required', 'date', 'before_or_equal:today'],
            'medio'       => ['required', 'string', 'max:60'],
            'nota'        => ['nullable', 'string', 'max:255'],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        // "105.000" o "$105.000" -> "105000"
        $this->merge([
            'valor' => $this->valor === null ? null : preg_replace('/[^\d]/', '', (string) $this->valor),
        ]);
    }



    public function messages(): array
    {
        return [
            'valor.required'        => 'Escribe el valor del pago.',
            'valor.min'             => 'El valor del pago debe ser mayor que cero.',
            'fecha.required'        => 'Escribe la fecha del pago.',
            'fecha.before_or_equal' => 'La fecha del pago no puede ser en el futuro.',
            'medio.required'        => 'Escribe el medio de pago (Nequi, efectivo...).',
            'comprobante.mimes'     => 'El comprobante debe ser una foto (JPG, PNG, WEBP) o un PDF.',
            'comprobante.max'       => 'El comprobante pesa más de 5 MB.',
        ];
    }
}
