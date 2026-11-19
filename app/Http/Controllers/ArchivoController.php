<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArchivoRequest;
use App\Http\Requests\UpdateArchivoRequest;
use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{

    public function index(Request $request)
    {
        $categoria = array_key_exists($request->categoria, Archivo::CATEGORIAS) ? $request->categoria : 'documentos';

        $archivos = Archivo::where('categoria', $categoria)
            ->orderByRaw('numero IS NULL')
            ->orderBy('numero')
            ->orderBy('titulo')
            ->get();

        $conteos = Archivo::selectRaw('categoria, COUNT(*) AS total')->groupBy('categoria')->pluck('total', 'categoria');

        return view('archivos.index', compact('categoria', 'archivos', 'conteos'));
    }



    public function store(StoreArchivoRequest $request)
    {
        $categoria = $request->validated()['categoria'];
        $subidos   = 0;

        DB::transaction(function () use ($request, $categoria, &$subidos) {
            foreach ($request->file('archivos') as $archivo) {
                [$numero, $titulo] = Archivo::tituloDesdeNombre($archivo->getClientOriginalName());

                Archivo::create([
                    'categoria'       => $categoria,
                    'titulo'          => mb_substr($titulo, 0, 150),
                    'numero'          => $numero,
                    'ruta'            => $archivo->store('archivos/' . $categoria),
                    'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 255),
                    'tipo_mime'       => $archivo->getMimeType() ?? 'application/octet-stream',
                    'tamano'          => $archivo->getSize(),
                    'usuario_id'      => auth()->id(),
                ]);

                $subidos++;
            }
        });

        return redirect()->route('archivos.index', ['categoria' => $categoria])
            ->with('mensajeCreado', $subidos === 1 ? 'Archivo subido.' : $subidos . ' archivos subidos.');
    }



    //  Abre el archivo en el navegador o lo descarga. Un PDF o una imagen se
    // abre tal cual; un Word, Excel o PowerPoint se ve por su PDF de vista
    // previa. Los archivos están en una carpeta privada: solo se llega a
    // ellos por aquí, con la sesión y el rol revisados.
    public function show(Request $request, Archivo $archivo)
    {
        if (! Storage::exists($archivo->ruta)) {
            return back()->with('error', 'No se encontró el archivo de "' . $archivo->titulo . '".');
        }

        if ($request->boolean('descargar') || ! $archivo->tieneVistaPrevia()) {
            return Storage::download($archivo->ruta, $archivo->titulo . '.' . $archivo->extension);
        }

        if ($archivo->seVeEnNavegador()) {
            return Storage::response($archivo->ruta, $archivo->titulo . '.' . $archivo->extension, [], 'inline');
        }

        if (! Storage::exists($archivo->vista_previa)) {
            return back()->with('error', 'No se encontró la vista previa de "' . $archivo->titulo . '".');
        }

        return Storage::response($archivo->vista_previa, $archivo->titulo . '.pdf', [], 'inline');
    }



    //  Pone o cambia el PDF de vista previa de un Word, Excel o PowerPoint
    public function vistaPrevia(Request $request, Archivo $archivo)
    {
        $request->validate(
            ['vista_previa' => ['required', 'file', 'mimes:pdf', 'max:' . Archivo::TAMANO_MAXIMO]],
            [
                'vista_previa.required' => 'Elige el PDF de la vista previa.',
                'vista_previa.mimes'    => 'La vista previa debe ser un PDF.',
                'vista_previa.max'      => 'El PDF pesa más de 20 MB.',
            ]
        );

        if ($archivo->seVeEnNavegador()) {
            return back()->with('mensajeAlerta', '"' . $archivo->titulo . '" ya se ve en la página: no necesita vista previa.');
        }

        $ruta = Archivo::rutaVistaPrevia($archivo->ruta);
        Storage::put($ruta, file_get_contents($request->file('vista_previa')->getRealPath()));
        $archivo->update(['vista_previa' => $ruta]);

        return redirect()->route('archivos.index', ['categoria' => $archivo->categoria])
            ->with('mensajeActualizado', 'Vista previa de "' . $archivo->titulo . '" lista.');
    }



    public function update(UpdateArchivoRequest $request, Archivo $archivo)
    {
        // Cargar datos sin guardar
        $archivo->fill($request->validated());

        // No hubo cambios
        if (! $archivo->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $archivo->save();

        return redirect()->route('archivos.index', ['categoria' => $archivo->categoria])->with('mensajeActualizado', '"' . $archivo->titulo . '" actualizado.');
    }



    //  Quitar un archivo lo borra también de la carpeta: si se dejara,
    // quedaría ocupando espacio sin forma de llegar a él.
    public function destroy(Archivo $archivo)
    {
        $categoria = $archivo->categoria;

        Storage::delete(array_filter([$archivo->ruta, $archivo->vista_previa]));
        $archivo->delete();

        return redirect()->route('archivos.index', ['categoria' => $categoria])->with('mensajeActualizado', '"' . $archivo->titulo . '" quitado.');
    }
}
