<?php

namespace App\Console\Commands;

use App\Models\Archivo;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ImportarArchivos extends Command
{
    protected $signature = 'archivos:importar {carpeta : Carpeta con los archivos} {categoria : documentos, charlas o letreros}';

    protected $description = 'Carga todos los archivos de una carpeta en Documentos, Charlas o Letreros';



    //  Para cargar de una vez las carpetas que ya existen (por ejemplo,
    // la de charlas) sin subirlas una por una. Un archivo con el mismo
    // nombre que ya esté en esa categoría se salta, así se puede repetir.
    public function handle(): int
    {
        $carpeta   = rtrim($this->argument('carpeta'), '\\/');
        $categoria = $this->argument('categoria');

        if (! array_key_exists($categoria, Archivo::CATEGORIAS)) {
            $this->error('La categoría debe ser: ' . implode(', ', array_keys(Archivo::CATEGORIAS)) . '.');
            return self::FAILURE;
        }

        if (! is_dir($carpeta)) {
            $this->error('No existe la carpeta: ' . $carpeta);
            return self::FAILURE;
        }

        $cargados = 0;
        $saltados = 0;

        foreach (scandir($carpeta) as $nombreArchivo) {
            $ruta      = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            // Solo archivos permitidos; se ignoran carpetas y temporales de Office (~$…)
            if (! is_file($ruta) || str_starts_with($nombreArchivo, '~$') || ! in_array($extension, Archivo::EXTENSIONES, true)) {
                continue;
            }

            if (Archivo::where('categoria', $categoria)->where('nombre_original', $nombreArchivo)->exists()) {
                $this->line('  ya estaba: ' . $nombreArchivo);
                $saltados++;
                continue;
            }

            $archivo = new File($ruta);
            [$numero, $titulo] = Archivo::tituloDesdeNombre($nombreArchivo);

            Archivo::create([
                'categoria'       => $categoria,
                'titulo'          => mb_substr($titulo, 0, 150),
                'numero'          => $numero,
                'ruta'            => Storage::putFile('archivos/' . $categoria, $archivo),
                'nombre_original' => mb_substr($nombreArchivo, 0, 255),
                'tipo_mime'       => $archivo->getMimeType() ?? 'application/octet-stream',
                'tamano'          => $archivo->getSize(),
            ]);

            $this->info('  cargado: ' . $nombreArchivo);
            $cargados++;
        }

        $this->newLine();
        $this->info($cargados . ' cargados, ' . $saltados . ' ya estaban.');

        return self::SUCCESS;
    }
}
