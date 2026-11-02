<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarAjustesRequest;
use App\Mail\ConfirmacionInscripcion;
use App\Models\Ajuste;
use App\Models\Inscripcion;
use App\Models\Retiro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AjusteController extends Controller
{
    public function index()
    {
        $claves = [Ajuste::WHATSAPP, Ajuste::CORREO_ASUNTO, Ajuste::CORREO_TEXTO, Ajuste::CORREO_AUTOMATICO];

        return view('ajustes.index', [
            'ajustes'      => collect($claves)->mapWithKeys(fn ($clave) => [$clave => Ajuste::valor($clave)]),
            'tieneQr'      => $this->rutaQr() !== null,
            'hayEjemplo'   => (bool) $this->inscripcionDeEjemplo(),
            'correoActivo' => config('mail.default') !== 'log',
        ]);
    }



    //  Guarda solo las claves que cambiaron. Si el valor vuelve a ser el
    // de fábrica, igual se guarda: así queda claro que alguien lo eligió.
    // La imagen del QR va aparte: se guarda en una carpeta privada.
    public function update(GuardarAjustesRequest $request)
    {
        $cambiados = 0;
        $textos    = collect($request->validated())->except(['qr', 'quitar_qr']);

        DB::transaction(function () use ($textos, &$cambiados) {
            foreach ($textos as $clave => $valor) {
                $ajuste = Ajuste::firstOrNew(['clave' => $clave]);
                $antes  = $ajuste->exists ? $ajuste->valor : Ajuste::valor($clave);

                if ($antes === $valor) {
                    continue;
                }

                $ajuste->valor = $valor;
                $ajuste->save();
                $cambiados++;
            }
        });

        $anterior = $this->rutaQr();

        if ($request->hasFile('qr')) {
            $ruta = $request->file('qr')->store('ajustes');

            Ajuste::updateOrCreate(['clave' => Ajuste::WHATSAPP_QR], ['valor' => $ruta]);
            $cambiados++;

            if ($anterior) {
                Storage::delete($anterior);
            }
        } elseif ($request->validated('quitar_qr') && $anterior) {
            Ajuste::where('clave', Ajuste::WHATSAPP_QR)->delete();
            Storage::delete($anterior);
            $cambiados++;
        }

        // No hubo cambios
        if (! $cambiados) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        return back()->with('mensajeActualizado', 'Ajustes guardados.');
    }



    //  La imagen del QR está en una carpeta privada; se ve pasando por
    // aquí, con la sesión y el rol revisados (la usa el directorio).
    public function qr()
    {
        $ruta = $this->rutaQr();

        abort_unless($ruta, 404);

        return Storage::response($ruta);
    }



    //  Muestra el correo tal como le llegaría a una persona inscrita en el
    // retiro actual, sin enviarlo.
    public function vistaPrevia()
    {
        $inscripcion = $this->inscripcionDeEjemplo();

        abort_unless($inscripcion, 404);

        return new ConfirmacionInscripcion($inscripcion);
    }



    private function rutaQr(): ?string
    {
        $ruta = Ajuste::valor(Ajuste::WHATSAPP_QR);

        return $ruta && Storage::exists($ruta) ? $ruta : null;
    }



    private function inscripcionDeEjemplo(): ?Inscripcion
    {
        $retiro = Retiro::actual();

        return $retiro ? $retiro->inscripciones()->with(['persona', 'retiro'])->first() : null;
    }
}
