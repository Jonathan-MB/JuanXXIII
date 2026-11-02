<?php

namespace App\Http\Requests;

use App\Models\SantisimoRegla;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReglaSantisimoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  El día vacío significa todos los días. Para llegar hasta la
    // medianoche se escribe 23:59.
    public function rules(): array
    {
        return [
            'equipo_id'    => ['required', 'integer', 'exists:equipos,id'],
            'tipo'         => ['required', Rule::in([SantisimoRegla::FIJA, SantisimoRegla::PROHIBIDA])],
            'dia_relativo' => ['nullable', 'integer', 'between:0,2'],
            'hora_desde'   => ['required', 'date_format:H:i:s'],
            'hora_hasta'   => ['required', 'date_format:H:i:s', 'after:hora_desde'],
        ];
    }



    protected  function prepareForValidation(): void
    {
        $this->merge([
            'equipo_id'    => $this->equipo,
            'dia_relativo' => $this->diaRelativo === '' ? null : $this->diaRelativo,
            'hora_desde'   => substr((string) $this->horaDesde, 0, 5) . ':00',
            'hora_hasta'   => substr((string) $this->horaHasta, 0, 5) . ':00',
        ]);
    }



    public function messages(): array
    {
        return [
            'equipo_id.required'     => 'Elige el equipo.',
            'equipo_id.exists'       => 'Ese equipo ya no existe. Recarga la página.',
            'tipo.in'                => 'Elige si la hora es fija o si el equipo no puede ir.',
            'dia_relativo.between'   => 'El día debe ser viernes, sábado o domingo.',
            'hora_desde.date_format' => 'Escribe la hora en que empieza.',
            'hora_hasta.date_format' => 'Escribe la hora en que termina.',
            'hora_hasta.after'       => 'La hora final debe ser después de la inicial. Para llegar a la medianoche, escribe 11:59 p. m.',
        ];
    }
}
