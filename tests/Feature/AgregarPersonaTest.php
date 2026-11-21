<?php

namespace Tests\Feature;

use App\Models\Inscripcion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  Secretaría · Agregar persona: quien llega sin inscribirse entra con datos
// incompletos. La inscripción pública, en cambio, sigue pidiendo todo.
class AgregarPersonaTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    public function test_secretaria_agrega_a_alguien_solo_con_nombres_y_apellidos(): void
    {
        $this->prepararRetiro();
        $this->actingAs($this->usuario());

        $this->post(route('llegada.agregar'), ['sexo' => 'Femenino', 'nombres' => 'Ana', 'apellidos' => 'Prueba Uno', 'ciudad' => '', 'cedula' => ''])
            ->assertRedirect(route('llegada.index', ['sexo' => 'mujeres']))
            ->assertSessionHas('mensajeCreado');

        $ana = Persona::where('nombres', 'Ana')->firstOrFail();
        $this->assertNull($ana->cedula);
        $this->assertNull($ana->fecha_nacimiento);
        $this->assertNull($ana->talla_camiseta);
        $this->assertSame(Inscripcion::INSCRITO, $ana->inscripciones()->first()->estado);

        // Sale en Llegada y en su ficha sin romper nada, con lo pendiente marcado
        $this->get(route('llegada.index', ['sexo' => 'mujeres']))->assertOk()->assertSee('Prueba Uno');
        $this->get(route('inscritos.show', $ana->inscripciones()->first()))->assertOk()->assertSee('Pendiente');

        // Dos personas sin cédula no chocan
        $this->post(route('llegada.agregar'), ['sexo' => 'Femenino', 'nombres' => 'Otra', 'apellidos' => 'Sin Cédula'])->assertSessionHas('mensajeCreado');
        $this->assertSame(2, Persona::whereNull('cedula')->count());

        // Y en su fila se completa después
        $inscripcion = $ana->inscripciones()->first();
        $this->patchJson(route('llegada.datos', $inscripcion), [
            'nombres' => 'Ana', 'apellidos' => 'Prueba Uno', 'cedula' => '', 'fechaNacimiento' => '1990-01-15',
            'tallaCamiseta' => 'S', 'ciudad' => 'Cali', 'barrio' => 'El Centro', 'contactos' => [[], []],
        ])->assertOk();
        $this->assertSame('S', $ana->refresh()->talla_camiseta);
        $this->assertNull($ana->cedula);
    }



    public function test_no_agrega_a_quien_ya_esta_en_la_lista(): void
    {
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
        $juan = $this->inscrito('Masculino');

        $this->post(route('llegada.agregar'), ['sexo' => 'Masculino', 'nombres' => 'Juan', 'apellidos' => 'Otro', 'cedula' => $juan->persona->cedula])
            ->assertSessionHas('error');
        $this->assertSame(1, Inscripcion::count());
    }



    public function test_la_inscripcion_publica_sigue_pidiendo_todos_los_datos(): void
    {
        $this->prepararRetiro(['inscripciones_abiertas' => true]);

        $this->post(route('inscripcion.store'), ['sexo' => 'Femenino', 'nombres' => 'Ana', 'apellidos' => 'García'])
            ->assertSessionHasErrors(['cedula', 'fecha_nacimiento', 'celular', 'barrio', 'ciudad', 'eps', 'talla_camiseta', 'contactos_emergencia']);

        $this->assertSame(0, Persona::count());
    }
}
