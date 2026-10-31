<?php

namespace App\Http\Requests;

use App\Models\Archivo;
use Illuminate\Foundation\Http\FormRequest;

class StoreArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    //  Se pueden subir varios archivos a la vez; el título de cada uno
    // sale del nombre del archivo y después se puede cambiar.
    public function rules(): array
    {
        return [
            'categoria'  => ['required', 'in:' . implode(',', array_keys(Archivo::CATEGORIAS))],
            'archivos'   => ['required', 'array', 'max:20'],
            'archivos.*' => ['file', 'mimes:' . implode(',', Archivo::EXTENSIONES), 'max:' . Archivo::TAMANO_MAXIMO],
        ];
    }



    public function messages(): array
    {
        return [
            'archivos.required' => 'Elige al menos un archivo.',
            'archivos.max'      => 'Se pueden subir máximo 20 archivos a la vez.',
            'archivos.*.mimes'  => 'Uno de los archivos no es PDF, Word, Excel, PowerPoint ni imagen.',
            'archivos.*.max'    => 'Uno de los archivos pesa más de 20 MB.',
            'archivos.*.file'   => 'Uno de los archivos no se pudo subir. Intenta de nuevo.',
        ];
    }
}
