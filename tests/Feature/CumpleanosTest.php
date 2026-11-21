<?php

namespace Tests\Feature;

use App\Models\Mesa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  La hoja de cumpleaños: retiristas, directivos y catequistas que cumplen
// desde 5 días antes del retiro hasta 5 días después. Datos inventados.
class CumpleanosTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
    }



    // Fecha de nacimiento en 1990 con el mismo día y mes que $fecha
    private function nacioEl($fecha): string
    {
        return '1990-' . $fecha->format('m-d');
    }



    public function test_trae_retiristas_directivos_y_catequistas_dentro_del_rango(): void
    {
        $inicio = $this->retiro->fecha_inicio;
        $fin    = $this->retiro->fecha_fin;

        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Cumple', 'apellidos' => 'En el retiro', 'fecha_nacimiento' => $this->nacioEl($inicio)]], 1, 1);
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Fuera', 'apellidos' => 'Del rango', 'fecha_nacimiento' => $this->nacioEl($fin->copy()->addDays(6))]], 1, 2);
        $this->inscrito('Femenino', ['persona' => ['nombres' => 'Cumple', 'apellidos' => 'Cinco después', 'fecha_nacimiento' => $this->nacioEl($fin->copy()->addDays(5))]], 1, 1);

        //  Directivo con cumpleaños desde Datos para imprimir
        $this->put(route('impresiones.rectorado'), [
            'sexo'   => 'Masculino',
            'cargos' => [
                ['cargo' => 'Rector(a)', 'nombre' => 'Rector Inventado', 'fecha_nacimiento' => $this->nacioEl($inicio->copy()->subDays(5))],
                ['cargo' => 'Vicerrector(a)', 'nombre' => 'Vicerrector Sin Fecha'],
            ],
        ])->assertRedirect();

        //  Catequista con cumpleaños desde Mesas
        $mesa = Mesa::where('sexo', 'Masculino')->where('numero', 1)->first();
        $this->put(route('mesas.update', $mesa), [
            'cantidadSillas'     => 8,
            'catequistaTitular'  => 'Catequista Inventado',
            'titularNacimiento'  => $this->nacioEl($fin),
            'catequistaAuxiliar' => 'Auxiliar Sin Fecha',
        ])->assertRedirect();
        $this->assertSame($this->nacioEl($fin), $mesa->fresh()->titular_nacimiento->toDateString());

        $this->get(route('impresiones.cumpleanos'))
            ->assertOk()
            ->assertSee('Cumple En el retiro')
            ->assertSee('Retirista · Mesa 1 · Silla 1')
            ->assertSee('Rector Inventado')
            ->assertSee('Directivo · Rector(a)')
            ->assertSee('Catequista Inventado')
            ->assertSee('Catequista titular (CT) · Mesa 1')
            ->assertSee('Cumple Cinco después')
            ->assertDontSee('Fuera Del rango')
            ->assertSee('cumple-en-retiro', false)
            ->assertSee('Sin cumpleaños anotado')
            ->assertSee('Vicerrector Sin Fecha')
            ->assertSee('Auxiliar Sin Fecha')
            //  Hombres y mujeres juntos, en orden de fecha, y renglones en blanco al final
            ->assertSeeInOrder(['Rector Inventado', 'Cumple En el retiro', 'Catequista Inventado', 'Cumple Cinco después', 'renglon-cumple'], false);

        //  La Hoja 3 trae los datos del catequista que ya están en Mesas
        $mesa->update(['titular_celular' => '3009998877', 'titular_ciudad' => 'Palmira', 'titular_barrio' => 'Barrio Inventado']);
        $this->get(route('impresiones.correcciones', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSeeInOrder(['Catequista Inventado', $fin->format('d/m') . '/1990', 'Palmira', 'Barrio Inventado', '3009998877', 'Auxiliar Sin Fecha']);

        $this->get(route('impresiones.cumpleanos', ['sexo' => 'hombres']))
            ->assertOk()->assertSee('Rector Inventado')->assertDontSee('Cumple Cinco después');
    }
}
