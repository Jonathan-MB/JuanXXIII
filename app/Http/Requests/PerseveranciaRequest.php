<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerseveranciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'tipo'          => ['required', 'string', 'max:40'],
            'sector'        => ['nullable', 'string', 'max:60'],
            'dia'           => ['required', 'string', 'max:30'],
            'hora'          => ['required', 'string', 'max:20'],
            'parroquia'     => ['required', 'string', 'max:120'],
            'direccion'     => ['nullable', 'string', 'max:120'],
            'mapa'          => ['nullable', 'string', 'max:160'],
            'coordenadas'   => ['nullable', 'regex:/^-?\d{1,2}(\.\d+)?,\s*-?\d{1,3}(\.\d+)?$/'],
            'latitud'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitud'      => ['nullable', 'numeric', 'between:-180,180'],
            'actividades'   => ['nullable', 'string', 'max:500'],
            'en_inicio'     => ['required', 'boolean'],
            'orden'         => ['required', 'integer', 'between:1,999'],
            'imagen'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'quitar_imagen' => ['required', 'boolean'],
            'imagen_credito' => ['nullable', 'string', 'max:200'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        //  Las coordenadas llegan juntas ("3.4372, -76.5225") y se guardan
        // en dos columnas; vacías quitan el punto del mapa
        $coordenadas = trim((string) $this->coordenadas);
        $partes      = array_map('trim', explode(',', $coordenadas));

        $this->merge([
            'coordenadas'   => $coordenadas ?: null,
            'latitud'       => $coordenadas && count($partes) === 2 ? $partes[0] : null,
            'longitud'      => $coordenadas && count($partes) === 2 ? $partes[1] : null,
            'tipo'          => trim((string) $this->tipo),
            'sector'        => trim((string) $this->sector) ?: null,
            'dia'           => trim((string) $this->dia),
            'hora'          => trim((string) $this->hora),
            'parroquia'     => trim((string) $this->parroquia),
            'direccion'     => trim((string) $this->direccion) ?: null,
            'mapa'          => trim((string) $this->mapa) ?: null,
            'actividades'   => trim(str_replace("\r\n", "\n", (string) $this->actividades)) ?: null,
            'en_inicio'     => $this->boolean('enInicio'),
            'quitar_imagen' => $this->boolean('quitarImagen'),
            'imagen_credito' => trim((string) $this->imagenCredito) ?: null,
        ]);
    }



    public function messages(): array
    {
        return [
            'tipo.required'      => 'Escribe el tipo (por ejemplo, Perseverancia).',
            'dia.required'       => 'Escribe el día.',
            'hora.required'      => 'Escribe la hora.',
            'parroquia.required' => 'Escribe la parroquia o el lugar.',
            'orden.required'     => 'Escribe el orden en que sale (1, 2, 3…).',
            'orden.between'      => 'El orden va de 1 a 999.',
            'actividades.max'    => 'Las actividades pueden tener máximo 500 letras.',
            'coordenadas.regex'  => 'Las coordenadas van como las copia Google Maps: 3.4372, -76.5225',
            'imagen.image'       => 'La imagen debe ser una foto.',
            'imagen.mimes'       => 'La imagen debe ser PNG, JPG o WEBP.',
            'imagen.max'         => 'La imagen puede pesar máximo 4 MB.',
        ];
    }
}
