<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  El formulario público de inscripción: ya no pide dirección ni correo.
class InscripcionPublicaTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    private function datos(array $cambios = []): array
    {
        return $cambios + [
            'sexo'                => 'Masculino',
            'nombres'             => 'Pedro',
            'apellidos'           => 'Gómez',
            'cedula'              => '1112223334',
            'fechaNacimiento'     => '1990-05-10',
            'celular'             => '3105551234',
            'barrio'              => 'Centro',
            'ciudad'              => 'Cali',
            'tomaMedicamentos'    => '0',
            'eps'                 => 'Sura',
            'tallaCamiseta'       => 'L',
            'contactosEmergencia' => [
                ['nombre' => 'Rosa', 'parentesco' => 'Esposa', 'celular' => '3001112233'],
                ['nombre' => 'Luis', 'parentesco' => 'Hijo', 'celular' => '3004445566'],
            ],
            'formaPago'           => 'Efectivo',
            'tienePadrino'        => '0',
            'aceptaDatos'         => '1',
            'declaraRequisitos'   => '1',
        ];
    }



    public function test_el_formulario_no_pide_direccion_ni_correo(): void
    {
        $this->prepararRetiro(['inscripciones_abiertas' => true]);

        $this->get(route('inscripcion'))
            ->assertOk()
            ->assertDontSee('name="direccion"', false)
            ->assertDontSee('name="correo"', false);

        $this->post(route('inscripcion.store'), $this->datos())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('inscripcion.recibida'));

        $persona = Persona::where('cedula', '1112223334')->firstOrFail();
        $this->assertSame('', $persona->direccion);
        $this->assertNull($persona->correo);
    }



    public function test_quien_ya_estaba_registrado_conserva_su_direccion_y_su_correo(): void
    {
        $this->prepararRetiro(['inscripciones_abiertas' => true]);

        Persona::create([
            'nombres' => 'Pedro', 'apellidos' => 'Gómez', 'cedula' => '1112223334', 'sexo' => 'Masculino',
            'fecha_nacimiento' => '1990-05-10', 'correo' => 'pedro@correo.com', 'celular' => '3100000000',
            'direccion' => 'Calle 5 # 10-20', 'barrio' => 'Centro', 'ciudad' => 'Cali', 'eps' => 'Sura', 'talla_camiseta' => 'L',
        ]);

        $this->post(route('inscripcion.store'), $this->datos())->assertSessionHasNoErrors();

        $persona = Persona::where('cedula', '1112223334')->firstOrFail();
        $this->assertSame('Calle 5 # 10-20', $persona->direccion);
        $this->assertSame('pedro@correo.com', $persona->correo);
        $this->assertSame('3105551234', $persona->celular, 'Lo demás sí se actualiza');
    }
}
