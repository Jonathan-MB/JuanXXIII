<?php

namespace Tests\Feature;

use App\Models\Inscripcion;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  El buscador general: encuentra por nombre aunque falten tildes o haya
// una letra mal, y a cada cuenta le dice si lo puede abrir o quién lo tiene.
class BuscadorTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
    }



    public function test_encuentra_hojas_letreros_y_personas_aunque_esten_mal_escritos(): void
    {
        $this->actingAs($this->usuario());
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Héctor', 'apellidos' => 'Mosquera']], 3, 1);

        $this->get(route('buscar'))->assertOk()->assertSee('Prueba con:');

        $this->get(route('buscar', ['q' => 'camisets']))->assertOk()->assertSee('Camisetas por talla')->assertSee('Antes del retiro · para Librería');
        $this->get(route('buscar', ['q' => 'nequi']))->assertOk()->assertSee('Pago por Nequi')->assertSee(route('letreros.show', 'nequi'));
        $this->get(route('buscar', ['q' => 'hector mosquera']))->assertOk()->assertSee('Héctor Mosquera')->assertSee('Mesa 3, silla 1');
        $this->get(route('buscar', ['q' => 'santisimo']))->assertOk()->assertSee('Rotación al Santísimo en sala');

        // Mientras se escribe, la página pide solo los resultados
        $this->get(route('buscar', ['q' => 'carta', 'parcial' => 1]))->assertOk()->assertSee('Cartas al presbítero')->assertDontSee('<html', false);

        $this->get(route('buscar', ['q' => 'zzzxxyy']))->assertOk()->assertSee('No encontré nada');
    }



    public function test_directivos_ve_quien_tiene_lo_que_no_puede_abrir(): void
    {
        $this->actingAs($this->usuario(Rol::DIRECTIVOS));
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Seefue'], 'inscripcion' => ['estado' => Inscripcion::ABANDONO]], 1, 1);

        $this->get(route('buscar', ['q' => 'cartas presbitero']))->assertOk()->assertSee('Lo tiene Secretaría')->assertDontSee(route('impresiones.index'));
        $this->get(route('buscar', ['q' => 'directorio']))->assertOk()->assertSee(route('directivos.directorio'));
        $this->get(route('buscar', ['q' => 'seefue']))->assertOk()->assertDontSee('Seefue');
    }



    public function test_las_hojas_2_y_3_piden_la_fecha_de_nacimiento(): void
    {
        $this->actingAs($this->usuario());
        $this->inscrito('Masculino', [], 1, 1);

        $this->get(route('impresiones.mesasEnBlanco', ['sexo' => 'hombres']))->assertOk()->assertSee('Fecha de nacimiento');
        $this->get(route('impresiones.correcciones', ['sexo' => 'hombres']))->assertOk()->assertSee('Fecha de nacimiento');
    }



    //  «cesar», «CÉSAR» y «César» encuentran a la misma persona
    public function test_buscar_personas_no_distingue_tildes_ni_mayusculas(): void
    {
        $this->actingAs($this->usuario());
        $cesar = $this->inscrito('Masculino', ['persona' => ['nombres' => 'César', 'apellidos' => 'Muñoz']]);
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Pedro', 'apellidos' => 'Gómez']]);

        foreach (['cesar', 'CÉSAR', 'César', 'CESAR', 'munoz', 'cesar muñoz'] as $texto) {
            $this->get(route('inscritos.index', ['busqueda' => $texto]))->assertOk()->assertSee('César Muñoz')->assertDontSee('Pedro Gómez');
            $this->get(route('pagos.index', ['busqueda' => $texto]))->assertOk()->assertSee('César Muñoz');
        }
    }
}
