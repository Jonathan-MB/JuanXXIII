<?php

namespace App\Console\Commands;

use App\Models\Archivo;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ImportarArchivos extends Command
{
    protected $signature = 'archivos:importar
        {carpeta : Carpeta con los archivos}
        {categoria : documentos, charlas o letreros}
        {--vistas= : Carpeta con el PDF de vista previa de cada Word, Excel o PowerPoint (mismo nombre, terminado en .pdf)}';

    protected $description = 'Carga todos los archivos de una carpeta en Documentos, Charlas o Letreros';



    //  Para cargar de una vez las carpetas que ya existen (por ejemplo,
    // la de charlas) sin subirlas una por una. Un archivo con el mismo
    // nombre que ya esté en esa categoría se salta, así se puede repetir;
    // si le falta la vista previa y ahora sí está, se la pone.
    public function handle(): int
    {
        $carpeta   = rtrim($this->argument('carpeta'), '\/');
        $categoria = $this->argument('categoria');
        $vistas    = $this->option('vistas') ? rtrim($this->option('vistas'), '\/') : null;

        if (! array_key_exists($categoria, Archivo::CATEGORIAS)) {
            $this->error('La categoría debe ser: ' . implode(', ', array_keys(Archivo::CATEGORIAS)) . '.');
            return self::FAILURE;
        }

        foreach (array_filter([$carpeta, $vistas]) as $directorio) {
            if (! is_dir($directorio)) {
                $this->error('No existe la carpeta: ' . $directorio);
                return self::FAILURE;
            }
        }

        $cargados   = 0;
        $saltados   = 0;
        $sinVista   = [];

        foreach (scandir($carpeta) as $nombreArchivo) {
            $ruta      = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            // Solo archivos permitidos; se ignoran carpetas y temporales de Office (~$…)
            if (! is_file($ruta) || str_starts_with($nombreArchivo, '~$') || ! in_array($extension, Archivo::EXTENSIONES, true)) {
                continue;
            }

            $pdfVista = $vistas ? $vistas . DIRECTORY_SEPARATOR . pathinfo($nombreArchivo, PATHINFO_FILENAME) . '.pdf' : null;
            $existente = Archivo::where('categoria', $categoria)->where('nombre_original', $nombreArchivo)->first();

            if ($existente) {
                if (! $existente->tieneVistaPrevia() && $pdfVista && is_file($pdfVista)) {
                    $this->ponerVistaPrevia($existente, $pdfVista);
                    $this->info('  vista previa puesta: ' . $nombreArchivo);
                } else {
                    $this->line('  ya estaba: ' . $nombreArchivo);
                }
                $saltados++;
                continue;
            }

            $archivo = new File($ruta);
            [$numero, $titulo] = Archivo::tituloDesdeNombre($nombreArchivo);

            $nuevo = Archivo::create([
                'categoria'       => $categoria,
                'titulo'          => mb_substr($titulo, 0, 150),
                'numero'          => $numero,
                'ruta'            => Storage::putFile('archivos/' . $categoria, $archivo),
                'nombre_original' => mb_substr($nombreArchivo, 0, 255),
                'tipo_mime'       => $archivo->getMimeType() ?? 'application/octet-stream',
                'tamano'          => $archivo->getSize(),
            ]);

            if (! $nuevo->seVeEnNavegador()) {
                if ($pdfVista && is_file($pdfVista)) {
                    $this->ponerVistaPrevia($nuevo, $pdfVista);
                } else {
                    $sinVista[] = $nombreArchivo;
                }
            }

            $this->info('  cargado: ' . $nombreArchivo);
            $cargados++;
        }

        $this->newLine();
        $this->info($cargados . ' cargados, ' . $saltados . ' ya estaban.');

        if ($sinVista) {
            $this->warn('Sin vista previa (solo se pueden descargar): ' . implode(', ', $sinVista));
        }

        return self::SUCCESS;
    }



    private function ponerVistaPrevia(Archivo $archivo, string $pdf): void
    {
        $ruta = Archivo::rutaVistaPrevia($archivo->ruta);
        Storage::put($ruta, file_get_contents($pdf));
        $archivo->update(['vista_previa' => $ruta]);
    }
}
