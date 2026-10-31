<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarActividadesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    // Sirve para la base y para la copia del retiro (con charlista)
    public function rules(): array
    {
        return [
            'actividades'                => ['required', 'array'],
            'actividades.*.dia_relativo' => ['required', 'integer', 'between:0,2'],
            'actividades.*.hora_inicio'  => ['required', 'date_format:H:i'],
            'actividades.*.hora_fin'     => ['nullable', 'date_format:H:i'],
            'actividades.*.nombre'       => ['required', 'string', 'max:100'],
            'actividades.*.es_charla'    => ['boolean'],
            'actividades.*.charlista'    => ['nullable', 'string', 'max:100'],
            'actividades.*.quitar'       => ['boolean'],
        ];
    }



    // Los campos de cada fila llegan en camelCase
    protected  function prepareForValidation(): void
    {
        $actividades = collect($this->actividades ?? [])->map(fn ($fila) => [
            'dia_relativo' => $fila['diaRelativo'] ?? null,
            'hora_inicio'  => substr((string) ($fila['horaInicio'] ?? ''), 0, 5),
            'hora_fin'     => substr((string) ($fila['horaFin'] ?? ''), 0, 5) ?: null,
            'nombre'       => trim((string) ($fila['nombre'] ?? '')),
            'es_charla'    => (bool) ($fila['esCharla'] ?? false),
            'charlista'    => trim((string) ($fila['charlista'] ?? '')) ?: null,
            'quitar'       => (bool) ($fila['quitar'] ?? false),
        ]);

        $this->merge(['actividades' => $actividades->all()]);
    }



    public function messages(): array
    {
        return [
            'actividades.required'                  => 'No llegaron actividades para guardar.',
            'actividades.*.hora_inicio.required'    => 'Cada actividad necesita su hora de inicio.',
            'actividades.*.hora_inicio.date_format' => 'Hay una hora de inicio que no es válida.',
            'actividades.*.hora_fin.date_format'    => 'Hay una hora de fin que no es válida.',
            'actividades.*.nombre.required'         => 'Cada actividad necesita un nombre.',
            'actividades.*.nombre.max'              => 'El nombre de una actividad puede tener máximo 100 letras.',
        ];
    }
}
