<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Console\Command;

class CrearAdministrador extends Command
{
    protected $signature = 'retiro:crear-admin';

    protected $description = 'Crea una cuenta de Administración (para la primera vez en el servidor)';



    //  En producción no corre el seeder de cuentas demo, así que la
    // primera cuenta se crea aquí. La contraseña se pide sin mostrarla
    // y nunca queda escrita en ningún archivo.
    public function handle(): int
    {
        $rolId = Rol::where('nombre', Rol::ADMINISTRACION)->value('id');

        if (! $rolId) {
            $this->error('No existe el rol de Administración. Corre primero: php artisan db:seed --force');
            return self::FAILURE;
        }

        $nombre  = trim((string) $this->ask('Nombre para mostrar (por ejemplo ADMIN TI)'));
        $usuario = trim((string) $this->ask('Nombre de usuario para entrar'));

        if ($nombre === '' || $usuario === '') {
            $this->error('El nombre y el usuario son obligatorios.');
            return self::FAILURE;
        }

        if (Usuario::where('usuario', $usuario)->exists()) {
            $this->error('Ya existe una cuenta con el usuario «' . $usuario . '».');
            return self::FAILURE;
        }

        $contrasena = (string) $this->secret('Contraseña (mínimo 8 caracteres)');

        if (mb_strlen($contrasena) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');
            return self::FAILURE;
        }

        if ($this->secret('Repite la contraseña') !== $contrasena) {
            $this->error('Las dos contraseñas no coinciden.');
            return self::FAILURE;
        }

        Usuario::create([
            'nombre'     => $nombre,
            'usuario'    => $usuario,
            'contrasena' => $contrasena,
            'activo'     => true,
            'rol_id'     => $rolId,
        ]);

        $this->info('Cuenta creada. Ya se puede entrar con el usuario «' . $usuario . '».');

        return self::SUCCESS;
    }
}
