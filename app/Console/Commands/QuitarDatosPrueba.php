<?php

namespace App\Console\Commands;

use App\Models\Articulo;
use App\Models\Inscripcion;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Retiro;
use App\Models\Usuario;
use Database\Seeders\DatosPruebaSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QuitarDatosPrueba extends Command
{
    protected $signature = 'datos-prueba:quitar {--force : No preguntar}';

    protected $description = 'Quita todo lo que creó DatosPruebaSeeder, sin tocar los datos reales';

    //  Las cuentas demo de UsuarioSeeder (para entrar en local) no se tocan
    private const CUENTAS_BASE = ['admin', 'secretaria', 'tesoreria', 'cocina'];



    //  Solo borra lo que tiene las marcas del seeder: cédulas 990…, retiros
    // con «DATOS DE PRUEBA», mesas con catequistas «(prueba)», artículos
    // con la entrada «Cantidad inicial (prueba)» y cuentas …@demo.test por
    // equipo. Una mesa marcada que ya tenga a alguien real se deja.
    public function handle(): int
    {
        $personas  = Persona::where('cedula', 'like', '990%')->pluck('id');
        $retiros   = Retiro::where('que_incluye', DatosPruebaSeeder::MARCA_RETIRO)->pluck('id');
        $articulos = Articulo::whereHas('movimientos', fn ($q) => $q->where('nota', 'Cantidad inicial ' . DatosPruebaSeeder::MARCA))->pluck('id');
        $usuarios  = Usuario::where('correo', 'like', '%@demo.test')->whereNotIn('usuario', self::CUENTAS_BASE)->pluck('id');

        $this->info('Se va a quitar:');
        $this->line('  ' . $personas->count() . ' personas de prueba y sus inscripciones, pagos y contactos');
        $this->line('  ' . $retiros->count() . ' retiros de prueba: ' . Retiro::whereIn('id', $retiros)->get()->map(fn ($r) => $r->numero_mujeres . ' M / ' . $r->numero_hombres . ' H')->implode(', '));
        $this->line('  las mesas de prueba que queden vacías');
        $this->line('  ' . $articulos->count() . ' artículos de inventario de prueba');
        $this->line('  ' . $usuarios->count() . ' cuentas demo por equipo');

        if (! $this->option('force') && ! $this->confirm('¿Quitar todo eso?')) {
            $this->line('Cancelado.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($personas, $retiros, $articulos, $usuarios) {
            $inscripciones = Inscripcion::whereIn('persona_id', $personas)->orWhereIn('retiro_id', $retiros)->pluck('id');

            Pago::whereIn('inscripcion_id', $inscripciones)->delete();
            Inscripcion::whereIn('id', $inscripciones)->delete();   // los contactos se van con ella
            Persona::whereIn('id', $personas)->whereDoesntHave('inscripciones')->delete();

            Mesa::whereIn('retiro_id', $retiros)->delete();
            Mesa::where('catequista_titular', 'like', '%' . DatosPruebaSeeder::MARCA)
                ->whereDoesntHave('inscripciones')
                ->delete();

            Retiro::whereIn('id', $retiros)->delete();   // cuentas, contactos, desarrollo y Santísimo se van con él
            Articulo::whereIn('id', $articulos)->delete();   // y sus movimientos
            Usuario::whereIn('id', $usuarios)->delete();
        });

        $this->info('Listo: datos de prueba quitados.');

        return self::SUCCESS;
    }
}
