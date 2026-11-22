<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerseveranciaRequest;
use App\Models\Ajuste;
use App\Models\Perseverancia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerseveranciaController extends Controller
{

    //  Las perseverancias salen al pie del directorio y en el carrusel
    // del inicio. Se editan aquí cuando cambian días u horarios.
    public function index()
    {
        $perseverancias = Perseverancia::orderBy('orden')->get();
        $cuartoDia      = self::cuartoDia();

        return view('perseverancias.index', compact('perseverancias', 'cuartoDia'));
    }



    //  El Cuarto día: fecha, hora y lugar, los tres opcionales. Solo sale en
    // el directorio (nunca en el carrusel ni en el mapa del inicio).
    public static function cuartoDia(): array
    {
        return [
            'fecha' => Ajuste::valor(Ajuste::CUARTO_DIA_FECHA),
            'hora'  => Ajuste::valor(Ajuste::CUARTO_DIA_HORA),
            'lugar' => Ajuste::valor(Ajuste::CUARTO_DIA_LUGAR),
        ];
    }



    public function guardarCuartoDia(Request $request)
    {
        $data = $request->validate([
            'fecha' => ['nullable', 'string', 'max:80'],
            'hora'  => ['nullable', 'string', 'max:40'],
            'lugar' => ['nullable', 'string', 'max:200'],
        ]);

        foreach (['fecha' => Ajuste::CUARTO_DIA_FECHA, 'hora' => Ajuste::CUARTO_DIA_HORA, 'lugar' => Ajuste::CUARTO_DIA_LUGAR] as $campo => $clave) {
            $valor = trim((string) ($data[$campo] ?? ''));

            $valor === ''
                ? Ajuste::where('clave', $clave)->delete()
                : Ajuste::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        }

        return redirect()->route('perseverancias.index')->with('mensajeActualizado', 'Cuarto día guardado.');
    }



    public function create()
    {
        $perseverancia = new Perseverancia([
            'tipo'      => 'Perseverancia',
            'en_inicio' => true,
            'orden'     => (int) Perseverancia::max('orden') + 1,
        ]);

        return view('perseverancias.formulario', compact('perseverancia'));
    }



    public function store(PerseveranciaRequest $request)
    {
        $data = collect($request->validated())->except(['imagen', 'quitar_imagen', 'coordenadas'])->all();

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store(Perseverancia::CARPETA_IMAGENES);
        }

        $perseverancia = Perseverancia::create($data);

        return redirect()->route('perseverancias.index')->with('mensajeCreado', 'Perseverancia agregada: ' . $perseverancia->titulo);
    }



    public function edit(Perseverancia $perseverancia)
    {
        return view('perseverancias.formulario', compact('perseverancia'));
    }



    public function update(PerseveranciaRequest $request, Perseverancia $perseverancia)
    {
        // Cargar datos sin guardar
        $perseverancia->fill(collect($request->validated())->except(['imagen', 'quitar_imagen', 'coordenadas'])->all());

        $anterior = $perseverancia->imagen;

        if ($request->hasFile('imagen')) {
            $perseverancia->imagen = $request->file('imagen')->store(Perseverancia::CARPETA_IMAGENES);
        } elseif ($request->validated('quitar_imagen')) {
            $perseverancia->imagen = null;
        }

        // No hubo cambios
        if (! $perseverancia->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $perseverancia->save();

        // La imagen que se reemplazó o se quitó ya no se usa
        if ($anterior && $anterior !== $perseverancia->imagen) {
            Storage::delete($anterior);
        }

        return redirect()->route('perseverancias.index')->with('mensajeActualizado', 'Perseverancia actualizada: ' . $perseverancia->titulo);
    }



    public function destroy(Perseverancia $perseverancia)
    {
        if ($perseverancia->imagen) {
            Storage::delete($perseverancia->imagen);
        }

        $perseverancia->delete();

        return back()->with('mensajeActualizado', 'Se quitó la perseverancia: ' . $perseverancia->titulo);
    }



    //  La imagen está en una carpeta privada; se ve por aquí, también
    // desde la página pública (es solo la foto de la parroquia)
    public function imagen(Perseverancia $perseverancia)
    {
        abort_unless($perseverancia->imagen && Storage::exists($perseverancia->imagen), 404);

        return Storage::response($perseverancia->imagen, null, ['Cache-Control' => 'public, max-age=86400']);
    }
}
