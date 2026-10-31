<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            Rol::ADMINISTRACION,
            Rol::SECRETARIA,
            Rol::DIRECTIVOS,
            Rol::TESORERIA,
            Rol::EQUIPO,
        ];

        foreach ($roles as $nombre) {
            Rol::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
