<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        //  En producción NO se ejecuta: las cuentas reales se crean
        // desde la aplicación. Los correos .test no pueden existir.
        if (app()->environment('production')) {
            $this->command?->warn('  UsuarioSeeder omitido: en producción las cuentas se crean desde la app.');
            return;
        }

        $demo = [
            ['usuario' => 'admin',      'correo' => 'admin@demo.test',      'nombre' => 'Administración Demo', 'rol' => Rol::ADMINISTRACION, 'equipo' => 'Directores'],
            ['usuario' => 'secretaria', 'correo' => 'secretaria@demo.test', 'nombre' => 'Secretaría Demo',     'rol' => Rol::SECRETARIA,     'equipo' => 'Secretaría'],
            ['usuario' => 'tesoreria',  'correo' => 'tesoreria@demo.test',  'nombre' => 'Tesorería Demo',      'rol' => Rol::TESORERIA,      'equipo' => 'Tesorería'],
            ['usuario' => 'cocina',     'correo' => 'cocina@demo.test',     'nombre' => 'Cocina Demo',         'rol' => Rol::EQUIPO,         'equipo' => 'Cocina'],
        ];

        foreach ($demo as $cuenta) {
            Usuario::firstOrCreate(
                ['usuario' => $cuenta['usuario']],
                [
                    'nombre'     => $cuenta['nombre'],
                    'correo'     => $cuenta['correo'],
                    'contrasena' => env('SEED_PASS_DEMO', 'demo12345'),
                    'rol_id'     => Rol::where('nombre', $cuenta['rol'])->value('id'),
                    'equipo_id'  => Equipo::where('nombre', $cuenta['equipo'])->value('id'),
                ]
            );
        }

        $this->command?->info('  Demo -> usuarios admin, secretaria, tesoreria y cocina');
    }
}
