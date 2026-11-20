<?php

namespace Tests\Feature;

use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  Tesorería: quién pagó, quién falta, cuánto, por qué medio y el padrino.
// Llamadas: el invitado, sus dos contactos y el padrino por si no contestan.
class TesoreriaYLlamadasTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
    }



    public function test_la_lista_de_pagos_muestra_pagado_falta_medio_y_padrino(): void
    {
        $this->actingAs($this->usuario(Rol::TESORERIA));

        $abono = $this->inscrito('Masculino', ['persona' => ['nombres' => 'Abonó'], 'inscripcion' => ['padrino_nombre' => 'Julián Paz', 'padrino_telefono' => '3202576766']]);
        $abono->pagos()->create(['valor' => 125000, 'fecha' => now()->toDateString(), 'medio' => 'Efectivo']);
        $this->inscrito('Femenino', ['persona' => ['nombres' => 'Nopagó']]);

        $this->get(route('pagos.imprimir'))
            ->assertOk()
            ->assertSee('$125.000')                // pagado y lo que falta (la mitad de 250.000)
            ->assertSee('Efectivo')                // medio del pago registrado
            ->assertSee('Dijo: Nequi 315 572 4732') // quien no ha pagado: el medio que dijo, sin "a nombre de"
            ->assertDontSee('a nombre de Gilma')
            ->assertSee('Julián Paz')
            ->assertSee('3202576766');

        $this->get(route('pagos.index'))->assertOk()->assertSee('Efectivo')->assertSee('Medio');
    }



    public function test_las_llamadas_traen_los_dos_contactos_y_el_padrino(): void
    {
        $this->actingAs($this->usuario());

        $juan = $this->inscrito('Masculino', ['inscripcion' => ['padrino_nombre' => 'Hernán Rodríguez', 'padrino_telefono' => '3071823271']], 1, 1);

        $this->get(route('impresiones.llamadas', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee($juan->contactosEmergencia[0]->nombre)
            ->assertSee($juan->contactosEmergencia[1]->nombre)
            ->assertSee('Hernán Rodríguez')
            ->assertSee('Padrino de retiro')
            ->assertSee('3071823271');
    }


    public function test_la_lista_de_pagos_marca_a_quien_no_vino_y_suma_las_cuentas(): void
    {
        $this->actingAs($this->usuario(Rol::TESORERIA));

        $completo = $this->inscrito('Masculino');
        $completo->pagos()->create(['valor' => 250000, 'fecha' => now()->toDateString(), 'medio' => 'Efectivo']);
        $this->inscrito('Masculino'); // no ha pagado: falta 250.000
        $fuera = $this->inscrito('Masculino', ['inscripcion' => ['estado' => \App\Models\Inscripcion::NO_ASISTIO]]);
        $fuera->pagos()->create(['valor' => 50000, 'fecha' => now()->toDateString(), 'medio' => 'Efectivo']);

        $respuesta = $this->get(route('pagos.imprimir'))->assertOk()
            ->assertSee('No asistió')
            ->assertSee('fila-fuera', false)
            ->assertSee('Cuentas · Hombres');

        $html = $respuesta->getContent();
        $this->assertSame(2, substr_count($html, '✔'), 'Sale marcado quien pagó todo y quien no asistió');
        $this->assertStringContainsString('0.000', $html, 'Recogido: también lo que pagó quien no vino');
        $this->assertMatchesRegularExpression('/Falta por recoger<\/th>\s*<td>[$]250.000/', $html, 'A quien no vino no se le cobra lo que falta');
    }
}
