<?php

namespace App\Http\Requests;

use App\Models\Persona;
use Illuminate\Foundation\Http\FormRequest;

class GuardarAjustesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  El enlace de WhatsApp y la imagen del QR son opcionales: lo que no
    // esté, no sale en el directorio.
    public function rules(): array
    {
        return [
            'whatsapp_enlace'                => ['nullable', 'url:https', 'max:255'],
            'correo_confirmacion_asunto'     => ['required', 'string', 'max:150'],
            'correo_confirmacion_texto'      => ['required', 'string', 'max:3000'],
            'correo_confirmacion_automatico' => ['required', 'boolean'],
            'letrero_nequi_numero'           => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'letrero_nequi_titular'          => ['nullable', 'string', 'max:100'],
            'rosario_enlace'                 => ['nullable', 'url:https', 'max:255'],
            'rosario_dias'                   => ['nullable', 'string', 'max:60'],
            'rosario_hora'                   => ['nullable', 'string', 'max:30'],
            'rosario_pedidos'                => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'qr'                             => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'quitar_qr'                      => ['required', 'boolean'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'whatsapp_enlace'                => trim((string) $this->whatsappEnlace) ?: null,
            'correo_confirmacion_asunto'     => trim((string) $this->correoAsunto),
            'correo_confirmacion_texto'      => trim(str_replace("\r\n", "\n", (string) $this->correoTexto)),
            'correo_confirmacion_automatico' => $this->boolean('correoAutomatico') ? '1' : '0',
            'letrero_nequi_numero'           => Persona::normalizarTelefono($this->nequiNumero),
            'letrero_nequi_titular'          => trim((string) $this->nequiTitular) ?: null,
            'quitar_qr'                      => $this->boolean('quitarQr'),

            //  Vacíos se guardan como "" y no como null: con null volvería
            // el valor de fábrica y no habría cómo quitar el rosario
            'rosario_enlace'                 => trim((string) $this->rosarioEnlace),
            'rosario_dias'                   => trim((string) $this->rosarioDias),
            'rosario_hora'                   => trim((string) $this->rosarioHora),
            'rosario_pedidos'                => (string) Persona::normalizarTelefono($this->rosarioPedidos),
        ]);
    }



    public function messages(): array
    {
        return [
            'whatsapp_enlace.url'                 => 'El enlace de WhatsApp debe empezar por https:// (por ejemplo https://chat.whatsapp.com/...).',
            'whatsapp_enlace.max'                 => 'El enlace de WhatsApp es demasiado largo.',
            'correo_confirmacion_asunto.required' => 'Escribe el asunto del correo.',
            'correo_confirmacion_asunto.max'      => 'El asunto puede tener máximo 150 letras.',
            'correo_confirmacion_texto.required'  => 'Escribe el texto del correo.',
            'correo_confirmacion_texto.max'       => 'El texto del correo puede tener máximo 3000 letras.',
            'letrero_nequi_numero.regex'          => 'El número de Nequi solo puede tener números.',
            'rosario_enlace.url'                  => 'El enlace del rosario debe empezar por https:// (por ejemplo https://meet.google.com/...).',
            'rosario_pedidos.regex'               => 'El celular de pedidos de oración solo puede tener números.',
            'qr.image'                            => 'El código QR debe ser una imagen.',
            'qr.mimes'                            => 'El código QR debe ser PNG, JPG o WEBP.',
            'qr.max'                              => 'La imagen del QR puede pesar máximo 2 MB.',
        ];
    }
}
