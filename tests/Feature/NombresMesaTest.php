<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\NombreMesa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  Nombres de mesas: el santo de cada número se guarda una vez y cada mesa
// nueva sin nombre llega con él, en este retiro y en los próximos.
class NombresMesaTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
    }



    public function test_se_guardan_editan_y_quitan_los_nombres(): void
    {
        $this->get(route('mesas.nombres'))->assertOk()->assertSee('Nombres de mesas')->assertSee('name="nombres[Femenino][12]"', false);

        $this->put(route('mesas.guardarNombres'), [
            'nombres' => [
                'Masculino' => [1 => 'San Inventado', 2 => '  San Probado  '],
                'Femenino'  => [1 => 'Santa Ficticia'],
            ],
        ])->assertRedirect(route('mesas.nombres'));

        $this->assertSame('San Probado', NombreMesa::para('Masculino', 2));
        $this->assertSame('Santa Ficticia', NombreMesa::para('Femenino', 1));

        //  Editar uno y dejar otro vacío lo quita
        $this->put(route('mesas.guardarNombres'), [
            'nombres' => ['Masculino' => [1 => 'San Corregido', 2 => '']],
        ]);

        $this->assertSame('San Corregido', NombreMesa::para('Masculino', 1));
        $this->assertNull(NombreMesa::para('Masculino', 2));
    }



    public function test_una_mesa_nueva_llega_con_el_nombre_de_su_numero(): void
    {
        NombreMesa::create(['sexo' => 'Masculino', 'numero' => 3, 'nombre' => 'San Inventado']);

        //  Creada sola al escribir el puesto en Llegada (igual que LlegadaController)
        Mesa::create(['retiro_id' => $this->retiro->id, 'sexo' => 'Masculino', 'numero' => 3, 'cantidad_sillas' => 1]);

        $this->assertSame('San Inventado', Mesa::where('sexo', 'Masculino')->where('numero', 3)->value('nombre'));

        //  Agregada a mano sin nombre, y con nombre propio (ese gana)
        NombreMesa::create(['sexo' => 'Femenino', 'numero' => 7, 'nombre' => 'Santa Ficticia']);
        $this->post(route('mesas.store'), ['sexo' => 'Femenino', 'numero' => 7, 'cantidadSillas' => 8])->assertRedirect();
        $this->post(route('mesas.store'), ['sexo' => 'Femenino', 'numero' => 8, 'cantidadSillas' => 8, 'nombre' => 'Santa Escrita'])->assertRedirect();

        $this->assertSame('Santa Ficticia', Mesa::where('sexo', 'Femenino')->where('numero', 7)->value('nombre'));
        $this->assertSame('Santa Escrita', Mesa::where('sexo', 'Femenino')->where('numero', 8)->value('nombre'));
    }



    public function test_los_rotulos_traen_santo_imagen_y_numero(): void
    {
        NombreMesa::create(['sexo' => 'Femenino', 'numero' => 1, 'nombre' => 'Santa Clara']);
        NombreMesa::create(['sexo' => 'Femenino', 'numero' => 2, 'nombre' => 'Santa Inventada']);

        //  Sin mesas en el retiro: salen las de Nombres de mesas. Santa Clara trae imagen de la app; la inventada no
        $this->get(route('impresiones.rotulosMesa', ['sexo' => 'mujeres']))
            ->assertOk()
            ->assertSeeInOrder(['Santa Clara', 'Mesa <span>N.º 1</span>', 'Santa Inventada', 'Mesa <span>N.º 2</span>'], false)
            ->assertSee('img/santos/santa-clara.jpg', false)
            ->assertSee('sin-imagen', false);
    }



    public function test_se_sube_la_imagen_de_un_santo_nuevo(): void
    {
        \Illuminate\Support\Facades\Storage::fake();

        $this->put(route('mesas.guardarNombres'), [
            'nombres'  => ['Masculino' => [9 => 'San Inventado']],
            'imagenes' => ['Masculino' => [9 => new \Illuminate\Http\UploadedFile(public_path('img/santos/san-marcos.jpg'), 'santo.jpg', 'image/jpeg', null, true)]],
        ])->assertRedirect(route('mesas.nombres'));

        \Illuminate\Support\Facades\Storage::assertExists('santos/san-inventado.jpg');
        $this->assertSame(route('mesas.imagenSanto', 'san-inventado'), NombreMesa::urlImagen('San Inventado'));
        $this->get(route('mesas.imagenSanto', 'san-inventado'))->assertOk();
    }



    public function test_puede_poner_el_nombre_a_las_mesas_de_este_retiro_que_no_tienen(): void
    {
        $sinNombre = Mesa::create(['retiro_id' => $this->retiro->id, 'sexo' => 'Femenino', 'numero' => 5, 'cantidad_sillas' => 8]);
        $conNombre = Mesa::create(['retiro_id' => $this->retiro->id, 'sexo' => 'Femenino', 'numero' => 6, 'cantidad_sillas' => 8, 'nombre' => 'Santa Propia']);

        $this->put(route('mesas.guardarNombres'), [
            'nombres'           => ['Femenino' => [5 => 'Santa Nueva', 6 => 'Santa Otra']],
            'ponerEnEsteRetiro' => 1,
        ])->assertSessionHas('mensajeActualizado');

        $this->assertSame('Santa Nueva', $sinNombre->fresh()->nombre);
        $this->assertSame('Santa Propia', $conNombre->fresh()->nombre);
    }
}
