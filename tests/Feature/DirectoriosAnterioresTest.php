<?php

namespace Tests\Feature;

use App\Models\Retiro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  Los directorios de los retiros que ya terminaron se pueden volver a
// abrir (hombres, mujeres o los dos) desde su propia página (menú General).
class DirectoriosAnterioresTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    public function test_el_directorio_de_un_retiro_que_ya_paso(): void
    {
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Inventado']], 1, 1);
        $this->inscrito('Femenino', ['persona' => ['nombres' => 'Ficticia']], 1, 1);

        //  El retiro termina: ya no es el actual, pasa a los anteriores
        $retiro = Retiro::first();
        $retiro->update(['fecha_inicio' => now()->subDays(12), 'fecha_fin' => now()->subDays(10)]);

        //  Aparte de Impresiones, en su propia página del menú
        $this->get(route('impresiones.index'))->assertOk()->assertDontSee('Directorios de retiros anteriores');
        $this->get(route('directoriosAnteriores'))->assertOk()
            ->assertSee('Directorios de retiros anteriores')
            ->assertSee(route('impresiones.directorio', ['retiro' => $retiro->id, 'sexo' => 'mujeres']));

        $this->get(route('impresiones.directorio', ['retiro' => $retiro->id, 'sexo' => 'hombres']))->assertOk()
            ->assertSee('Inventado')->assertDontSee('Ficticia')->assertSee('Volver a directorios anteriores');
        $this->get(route('impresiones.directorio', ['retiro' => $retiro->id, 'sexo' => 'mujeres']))->assertOk()
            ->assertSee('Ficticia')->assertDontSee('Inventado');
        $this->get(route('impresiones.directorio', ['retiro' => $retiro->id]))->assertOk()
            ->assertSee('Inventado')->assertSee('Ficticia');

        $this->get(route('impresiones.directorio', ['retiro' => 9999]))->assertNotFound();
    }
}
