<?php

namespace Tests\Feature;

use App\Models\Inscripcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  Regla del retiro: las sillas no se cambian nunca. Si alguien se va, su
// silla queda vacía y no se le da a nadie más (pero sí se puede corregir
// el puesto de una persona si se escribió mal).
class SillasTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
    }



    public function test_quien_abandona_conserva_su_silla(): void
    {
        $juan = $this->inscrito('Masculino', [], 1, 3);

        $this->patch(route('inscritos.estado', $juan), ['estado' => Inscripcion::ABANDONO])->assertRedirect();

        $juan->refresh();
        $this->assertSame(Inscripcion::ABANDONO, $juan->estado);
        $this->assertNotNull($juan->mesa_id, 'Quien se fue debe conservar su mesa');
        $this->assertSame(3, (int) $juan->silla, 'Quien se fue debe conservar su silla');
    }



    public function test_la_silla_de_quien_se_fue_no_se_le_da_a_nadie(): void
    {
        $juan  = $this->inscrito('Masculino', ['inscripcion' => ['estado' => Inscripcion::ABANDONO]], 1, 3);
        $pedro = $this->inscrito('Masculino');

        $this->patch(route('llegada.puesto', $pedro), ['mesa' => 1, 'silla' => 3])
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'que se fue') && str_contains($mensaje, 'queda vacía'));

        $this->assertNull($pedro->refresh()->mesa_id);
    }



    public function test_quien_no_asistio_no_conserva_puesto(): void
    {
        $juan = $this->inscrito('Masculino', ['inscripcion' => ['estado' => Inscripcion::CONFIRMADO]], 2, 5);

        $this->patch(route('inscritos.estado', $juan), ['estado' => Inscripcion::NO_ASISTIO]);

        $juan->refresh();
        $this->assertNull($juan->mesa_id);
        $this->assertNull($juan->silla);
    }



    public function test_el_puesto_se_puede_corregir_sin_mover_a_los_demas(): void
    {
        $juan  = $this->inscrito('Masculino', [], 1, 1);
        $pedro = $this->inscrito('Masculino', [], 1, 2);

        $this->patch(route('llegada.puesto', $juan), ['mesa' => 1, 'silla' => 4])->assertSessionHas('mensajeActualizado');

        $this->assertSame(4, (int) $juan->refresh()->silla);
        $this->assertSame(2, (int) $pedro->refresh()->silla, 'Corregir a uno no mueve a otro');
    }



    public function test_dos_personas_no_quedan_en_la_misma_silla(): void
    {
        $this->inscrito('Masculino', [], 1, 1);
        $pedro = $this->inscrito('Masculino');

        $this->patch(route('llegada.puesto', $pedro), ['mesa' => 1, 'silla' => 1])->assertSessionHas('error');
        $this->assertNull($pedro->refresh()->mesa_id);
    }



    public function test_las_listas_impresas_dejan_vacia_la_silla_de_quien_se_fue(): void
    {
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Sigue']], 1, 1);
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Seefue'], 'inscripcion' => ['estado' => Inscripcion::ABANDONO]], 1, 2);

        //  Ninguna hoja lo trae (su silla queda en blanco), salvo el llamado y Tesorería
        $hojas = ['impresiones.correcciones', 'impresiones.carta', 'impresiones.directorio', 'impresiones.previstos',
            'impresiones.sencilla', 'impresiones.puertas', 'impresiones.familiares', 'impresiones.llamadas',
            'impresiones.camisetas', 'impresiones.ubicacion', 'impresiones.sobres', 'impresiones.llegada'];

        foreach ($hojas as $ruta) {
            // La carta escribe los nombres en mayúsculas: se compara sin importar eso
            $pagina = mb_strtolower($this->get(route($ruta, ['sexo' => 'hombres']))->assertOk()->getContent());
            $this->assertStringContainsString('sigue', $pagina, "En $ruta debe salir quien sigue");
            $this->assertStringNotContainsString('seefue', $pagina, "En $ruta no debe salir quien se fue");
        }

        $this->assertStringNotContainsString('Seefue', $this->get(route('impresiones.cumpleanos'))->assertOk()->getContent());

        foreach (['impresiones.llamado', 'pagos.imprimir'] as $ruta) {
            $this->assertStringContainsString('Seefue', $this->get(route($ruta, ['sexo' => 'hombres']))->assertOk()->getContent(), "En $ruta sí sale quien se fue");
        }

        //  La lista sencilla deja la silla 2 en blanco
        $this->assertMatchesRegularExpression('/>\s*2\s*<\/td>\s*<td[^>]*>\s*<\/td>/', $this->get(route('impresiones.sencilla', ['sexo' => 'hombres']))->getContent());
    }



    public function test_el_mapa_de_llegada_marca_la_silla_de_quien_se_fue(): void
    {
        $this->inscrito('Masculino', ['inscripcion' => ['estado' => Inscripcion::ABANDONO]], 1, 2);

        $this->get(route('llegada.index', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee('silla-se-fue', false)
            ->assertSee('0 de 8');
    }
}
