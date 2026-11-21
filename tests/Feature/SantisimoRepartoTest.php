<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\SantisimoFranja;
use App\Models\SantisimoRegla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  El reparto automático del Santísimo: respeta las horas prohibidas, no
// toca las franjas de TODOS y, con el retiro andando, no cambia lo que ya pasó.
class SantisimoRepartoTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    public function test_con_el_retiro_andando_solo_se_reparte_lo_que_falta(): void
    {
        $this->travelTo(now()->setTime(20, 15));
        $retiro = $this->prepararRetiro(['fecha_inicio' => now()->toDateString(), 'fecha_fin' => now()->addDays(2)->toDateString()]);
        $this->actingAs($this->usuario());

        $coro   = Equipo::where('nombre', 'Coro')->firstOrFail();
        $cocina = Equipo::where('nombre', 'Cocina')->firstOrFail();
        Equipo::query()->update(['participa_santisimo' => false]);
        Equipo::whereKey([$coro->id, $cocina->id])->update(['participa_santisimo' => true, 'cantidad_personas' => 4]);

        // La cocina no puede el viernes antes de las 9 pm
        SantisimoRegla::create(['equipo_id' => $cocina->id, 'tipo' => 'prohibida', 'dia_relativo' => 0, 'hora_desde' => '00:00:00', 'hora_hasta' => '21:00:00']);

        $hoy   = now()->toDateString();
        $paso  = SantisimoFranja::create(['retiro_id' => $retiro->id, 'inicio' => "$hoy 19:30:00", 'equipo_id' => $cocina->id]);
        $libre = SantisimoFranja::create(['retiro_id' => $retiro->id, 'inicio' => "$hoy 20:30:00"]);
        $todos = SantisimoFranja::create(['retiro_id' => $retiro->id, 'inicio' => "$hoy 21:00:00", 'todos' => true, 'actividad' => 'Hora santa']);
        $noche = SantisimoFranja::create(['retiro_id' => $retiro->id, 'inicio' => "$hoy 22:00:00"]);

        $this->post(route('santisimo.reparto.repartir'), ['destino' => 'retiro', 'turno' => 2])
            ->assertRedirect(route('santisimo.editar'));

        $this->assertSame($cocina->id, $paso->refresh()->equipo_id, 'Lo que ya pasó no se toca');
        $this->assertSame($coro->id, $libre->refresh()->equipo_id, 'A las 8:30 pm la cocina no puede: va el coro');
        $this->assertNull($todos->refresh()->equipo_id, 'La franja de TODOS no se asigna');
        $this->assertNotNull($noche->refresh()->equipo_id);
    }
}
