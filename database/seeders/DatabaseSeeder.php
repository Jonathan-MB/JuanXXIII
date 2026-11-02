<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            EquipoSeeder::class,
            UsuarioSeeder::class,
            FirmanteSeeder::class,
            PerseveranciaSeeder::class,
            SantisimoBaseSeeder::class,
            SantisimoReglaSeeder::class,
            ActividadBaseSeeder::class,
            FuncionesSecretariaSeeder::class,
        ]);
    }
}
