<?php

namespace Tests;

use App\Models\Equipo;
use App\Models\Inscripcion;
use App\Models\Mesa;
use App\Models\Persona;
use App\Models\Retiro;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\EquipoSeeder;
use Database\Seeders\RolSeeder;

//  Arma en pocas líneas lo que se necesita para probar un retiro: el
// retiro (empieza mañana, así es el «actual»), cuentas por rol y personas
// inscritas con contactos, padrino, pagos y puesto.
trait EscenarioRetiro
{
    protected Retiro $retiro;

    private int $cedulas = 0;



    protected function prepararRetiro(array $datos = []): Retiro
    {
        $this->seed([RolSeeder::class, EquipoSeeder::class]);

        return $this->retiro = Retiro::create($datos + [
            'numero_hombres'         => 90,
            'numero_mujeres'         => 89,
            'fecha_inicio'           => now()->addDay()->toDateString(),
            'hora_inicio'            => '17:00',
            'fecha_fin'              => now()->addDays(3)->toDateString(),
            'hora_fin'               => '19:00',
            'ofrenda'                => 250000,
            'porcentaje_reserva'     => 50,
            'lugar'                  => 'Colegio Nuestra Señora de Chiquinquirá',
            'ciudad'                 => 'Cali',
            'inscripciones_abiertas' => false,
        ]);
    }



    protected function usuario(string $rol = Rol::SECRETARIA, ?string $equipo = null): Usuario
    {
        $this->cedulas++;

        return Usuario::create([
            'nombre'     => $rol . ' prueba',
            'usuario'    => 'prueba' . $this->cedulas,
            'correo'     => 'prueba' . $this->cedulas . '@prueba.test',
            'contrasena' => 'clave-de-prueba',
            'rol_id'     => Rol::where('nombre', $rol)->value('id'),
            'equipo_id'  => $equipo ? Equipo::where('nombre', $equipo)->value('id') : null,
        ]);
    }



    //  Una persona inscrita en el retiro. Con $mesa y $silla queda sentada.
    protected function inscrito(string $sexo = 'Masculino', array $datos = [], ?int $mesa = null, ?int $silla = null): Inscripcion
    {
        $this->cedulas++;

        $persona = Persona::create(($datos['persona'] ?? []) + [
            'nombres'          => $sexo === 'Femenino' ? 'María' : 'Juan',
            'apellidos'        => 'Prueba ' . $this->cedulas,
            'cedula'           => (string) (880000000 + $this->cedulas),
            'sexo'             => $sexo,
            'fecha_nacimiento' => '1990-05-10',
            'correo'           => 'persona' . $this->cedulas . '@prueba.test',
            'celular'          => '3000000' . str_pad((string) $this->cedulas, 3, '0', STR_PAD_LEFT),
            'direccion'        => 'Calle 1 # 2-3',
            'barrio'           => 'El Rodeo',
            'ciudad'           => 'Cali',
            'eps'              => 'Sura',
            'talla_camiseta'   => 'M',
        ]);

        $mesaId = null;
        if ($mesa !== null) {
            $mesaId = Mesa::firstOrCreate(
                ['retiro_id' => $this->retiro->id, 'sexo' => $sexo, 'numero' => $mesa],
                ['cantidad_sillas' => 8, 'catequista_titular' => 'CT Mesa ' . $mesa, 'catequista_auxiliar' => 'CA Mesa ' . $mesa]
            )->id;
        }

        $inscripcion = Inscripcion::create(($datos['inscripcion'] ?? []) + [
            'estado'             => Inscripcion::LLEGO,
            'forma_pago'         => 'Nequi 315 572 4732 a nombre de Gilma Chávez',
            'tiene_padrino'      => true,
            'padrino_nombre'     => 'Padrino de ' . $persona->nombres,
            'padrino_telefono'   => '3110000000',
            'acepta_datos'       => true,
            'declara_requisitos' => true,
            'llegada_en'         => now(),
            'persona_id'         => $persona->id,
            'retiro_id'          => $this->retiro->id,
            'mesa_id'            => $mesaId,
            'silla'              => $silla,
        ]);

        foreach ([['Contacto Uno', 'Mamá', '3201111111'], ['Contacto Dos', 'Hermano', '3202222222']] as [$nombre, $parentesco, $celular]) {
            $inscripcion->contactosEmergencia()->create(['nombre' => $nombre . ' ' . $this->cedulas, 'parentesco' => $parentesco, 'celular' => $celular, 'telefono_fijo' => '6025555555']);
        }

        return $inscripcion;
    }
}
