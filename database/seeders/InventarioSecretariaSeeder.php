<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\Equipo;
use App\Models\MovimientoInventario;
use Illuminate\Database\Seeder;

//  El inventario de Secretaría del Excel «Inventario Secretaria.xlsx»
// (hoja de agosto de 2026, la última). No va en DatabaseSeeder: se corre
// una vez, a propósito, con
//   php artisan db:seed --class=InventarioSecretariaSeeder
// Un artículo que ya exista con el mismo nombre no se toca.
class InventarioSecretariaSeeder extends Seeder
{
    //  [artículo, cantidad que hay, unidad, por comprar]. La «1/2 resma»
    // de papel carta va como 250 hojas porque las cantidades son enteras.
    private const ARTICULOS = [
        ['Papelillo azul rey', 1, 'paquetes', 0],
        ['Papelillo azul cielo', 1, 'paquetes', 0],
        ['Papelillo rojo', 1, 'paquetes', 0],
        ['Papelillo rosado', 1, 'paquetes', 0],
        ['Papelillo amarillo', 1, 'paquetes', 0],
        ['Papelillo naranja', 1, 'paquetes', 0],
        ['Papelillo blanco', 1, 'paquetes', 0],
        ['Papelillo marrón', 1, 'paquetes', 0],
        ['Bolsa industrial grande', 6, 'unidades', 0],
        ['Papel celofán', 3, 'paquetes', 0],
        ['Cartulina pliego', 0, 'pliegos', 0],
        ['Marcador rojo', 15, 'unidades', 0],
        ['Marcador negro', 20, 'unidades', 0],
        ['Marcador azul', 11, 'unidades', 0],
        ['Resaltador amarillo', 12, 'unidades', 0],
        ['Resaltador rosa', 15, 'unidades', 0],
        ['Lápices', 65, 'unidades', 0],
        ['Colores', 150, 'unidades', 0],
        ['Borradores', 30, 'unidades', 0],
        ['Sacapuntas', 1, 'tarros', 0],
        ['Lapiceros', 4, 'cajas', 0],
        ['Papel carta', 250, 'hojas', 0],
        ['Papel oficio', 0, 'resmas', 0],
        ['Cinta transparente grande', 2, 'rollos', 0],
        ['Cinta de enmascarar', 2, 'rollos', 0],
        ['Platos de icopor medianos (para insignias)', 10, 'unidades', 0],
        ['Bombas R12 de colores surtidos', 0, 'paquetes', 1],
        ['Oraciones al Espíritu Santo tipo separador', 15, 'unidades', 0],
        ['Bolsas de papel para Ayudas de Dios', 22, 'unidades', 0],
        ['Bananas', 0, 'paquetes', 4],
    ];



    public function run(): void
    {
        $equipo = Equipo::where('nombre', 'Secretaría')->first();

        if (! $equipo) {
            $this->command?->warn('  No existe el equipo Secretaría.');
            return;
        }

        $nuevos = 0;

        foreach (self::ARTICULOS as [$nombre, $cantidad, $unidad, $porComprar]) {
            if (Articulo::where('equipo_id', $equipo->id)->where('nombre', $nombre)->exists()) {
                continue;
            }

            $articulo = Articulo::create([
                'equipo_id'   => $equipo->id,
                'nombre'      => $nombre,
                'unidad'      => $unidad,
                'cantidad'    => $cantidad,
                'por_comprar' => $porComprar,
            ]);

            // Queda en el historial de dónde salió la cantidad
            $articulo->movimientos()->create([
                'tipo'                => MovimientoInventario::AJUSTE,
                'cantidad'            => $cantidad,
                'cantidad_resultante' => $cantidad,
                'nota'                => 'Inventario de agosto de 2026 (Excel de Secretaría)',
            ]);

            $nuevos++;
        }

        $this->command?->info('  Inventario de Secretaría: ' . $nuevos . ' artículos cargados.');
    }
}
