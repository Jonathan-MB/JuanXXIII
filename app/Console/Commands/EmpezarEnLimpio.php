<?php

namespace App\Console\Commands;

use App\Models\Rol;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmpezarEnLimpio extends Command
{
    protected $signature = 'retiro:empezar-en-limpio
                            {--confirmar : Borrar de verdad (sin esto solo muestra lo que borraría)}
                            {--cuentas= : Otras cuentas de prueba para borrar, separadas por coma (por ejemplo prueba.flores)}';

    protected $description = 'Borra los datos de prueba (retiros, personas, pagos, inventario…) y deja la configuración';



    //  Para dejar la base lista antes de usarla de verdad. Borra todo lo
    // operativo y las cuentas @demo.test; conserva roles, equipos, las
    // cuentas reales, firmantes, perseverancias, los horarios base, la
    // lista de tareas (menos las "(prueba)") y los archivos subidos.
    public function handle(): int
    {
        $cuentasExtra = array_filter(array_map('trim', explode(',', (string) $this->option('cuentas'))));

        $cuentas = DB::table('usuarios')
            ->where('correo', 'like', '%@demo.test')
            ->when($cuentasExtra, fn ($consulta) => $consulta->orWhereIn('usuario', $cuentasExtra))
            ->pluck('usuario', 'id');

        // Nunca dejar la aplicación sin quién la administre
        $adminsQueQuedan = DB::table('usuarios')
            ->join('rols', 'rols.id', '=', 'usuarios.rol_id')
            ->where('rols.nombre', Rol::ADMINISTRACION)
            ->where('usuarios.activo', true)
            ->whereNotIn('usuarios.id', $cuentas->keys())
            ->pluck('usuarios.usuario');

        if ($adminsQueQuedan->isEmpty()) {
            $this->error('No se borra nada: no quedaría ninguna cuenta activa de Administración.');
            return self::FAILURE;
        }

        $resumen = [
            ['Retiros (con mesas, rectorados, horarios y cuentas de pago)', DB::table('retiros')->count()],
            ['Personas', DB::table('personas')->count()],
            ['Inscripciones (con contactos de emergencia)', DB::table('inscripcions')->count()],
            ['Pagos', DB::table('pagos')->count()],
            ['Artículos de inventario (con sus movimientos)', DB::table('articulos')->count()],
            ['Tareas de ejemplo "(prueba)"', DB::table('tarea_cronogramas')->where('descripcion', 'like', '%(prueba)')->count()],
            ['Cuentas de prueba (abajo cuáles)', $cuentas->count()],
            ['Comprobantes de pago guardados (archivos)', count(Storage::allFiles('comprobantes'))],
        ];

        $this->table(['Se borra', 'Cantidad'], $resumen);
        $this->line('Cuentas que se borran: ' . ($cuentas->isEmpty() ? 'ninguna' : $cuentas->implode(', ')));
        $this->line('Quedan las cuentas de Administración: ' . $adminsQueQuedan->implode(', '));

        if (! $this->option('confirmar')) {
            $this->newLine();
            $this->info('No se borró nada. Para borrar de verdad: php artisan retiro:empezar-en-limpio --confirmar');
            return self::SUCCESS;
        }

        // No tiene vuelta atrás: se pide escribir la palabra
        if (trim((string) $this->ask('Esto NO se puede deshacer. Escribe BORRAR para seguir')) !== 'BORRAR') {
            $this->warn('Cancelado: no se borró nada.');
            return self::FAILURE;
        }

        // En orden: primero lo que depende de otras tablas
        DB::transaction(function () use ($cuentas) {
            DB::table('pagos')->delete();
            DB::table('inscripcions')->delete();
            DB::table('personas')->delete();
            DB::table('mesas')->delete();
            DB::table('retiros')->delete();
            DB::table('articulos')->delete();
            DB::table('tarea_cronogramas')->where('descripcion', 'like', '%(prueba)')->delete();
            DB::table('usuarios')->whereIn('id', $cuentas->keys())->delete();
        });

        // Los archivos van después: si la base fallara, no se pierden
        Storage::deleteDirectory('comprobantes');

        $this->info('Listo: la base quedó lista para empezar. Ya se puede crear el primer retiro real.');

        return self::SUCCESS;
    }
}
