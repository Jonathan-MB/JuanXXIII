<?php

namespace Tests\Feature;

use App\Models\Ajuste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  El Cuarto día va aparte de las perseverancias: fecha, hora y lugar
// (todo opcional), solo en el directorio. Datos inventados.
class CuartoDiaTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
    }



    public function test_se_guarda_y_sale_en_el_directorio(): void
    {
        $this->get(route('perseverancias.index'))->assertOk()->assertSee('Cuarto día')->assertSee('name="lugar"', false);

        $this->put(route('perseverancias.cuartoDia'), [
            'fecha' => 'Jueves 3 de diciembre',
            'hora'  => '6:30 p. m.',
            'lugar' => 'Parroquia Inventada, Calle 1 # 2-3',
        ])->assertRedirect(route('perseverancias.index'));

        $this->assertSame('Parroquia Inventada, Calle 1 # 2-3', Ajuste::valor(Ajuste::CUARTO_DIA_LUGAR));

        $this->inscrito('Masculino', [], 1, 1);
        $this->get(route('impresiones.directorio', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee('cuarto-dia-directorio', false)
            ->assertSee('Jueves 3 de diciembre · 6:30 p. m.')
            ->assertSee('Parroquia Inventada, Calle 1 # 2-3');
    }



    public function test_puede_quedar_en_blanco_y_el_directorio_deja_el_espacio(): void
    {
        Ajuste::create(['clave' => Ajuste::CUARTO_DIA_FECHA, 'valor' => 'Jueves']);

        $this->put(route('perseverancias.cuartoDia'), ['fecha' => '', 'hora' => '', 'lugar' => ''])->assertRedirect();

        $this->assertNull(Ajuste::valor(Ajuste::CUARTO_DIA_FECHA));

        $this->inscrito('Femenino', [], 1, 1);
        $this->get(route('impresiones.directorio', ['sexo' => 'mujeres']))
            ->assertOk()
            ->assertSee('cuarto-dia-directorio', false)
            ->assertSee('espacio-cuarto-dia', false);
    }
}
