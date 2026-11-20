<?php

namespace Tests\Feature;

use App\Models\Inscripcion;
use App\Models\Retiro;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\EscenarioRetiro;
use Tests\TestCase;

//  Las hojas que imprime Secretaría en el orden del retiro, con el número,
// las fechas y el lugar del retiro que se esté trabajando.
class HojasDelRetiroTest extends TestCase
{
    use RefreshDatabase, EscenarioRetiro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararRetiro();
        $this->actingAs($this->usuario());
    }



    public function test_hoja_1_y_hoja_2_salen_en_blanco_aunque_no_haya_mesas(): void
    {
        $this->get(route('impresiones.nombresMesa', ['sexo' => 'hombres']))
            ->assertOk()->assertSee('Hoja 1 · Nombres')->assertSee('Mesa 10');

        $this->get(route('impresiones.mesasEnBlanco', ['sexo' => 'mujeres']))
            ->assertOk()->assertSee('Hoja 2 · Datos')->assertSee('Contacto de emergencia 2')->assertSee('Retiro N.º 89 Mujeres');
    }



    public function test_hoja_3_trae_los_datos_y_los_contactos(): void
    {
        $juan = $this->inscrito('Masculino', [], 1, 1);

        $this->get(route('impresiones.correcciones', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee($juan->persona->cedula)
            ->assertSee($juan->persona->barrio)
            ->assertSee($juan->contactosEmergencia[0]->nombre);
    }



    public function test_editar_datos_en_llegada_corrige_contactos_sin_perder_el_fijo(): void
    {
        $juan     = $this->inscrito('Masculino', [], 1, 1);
        $persona  = $juan->persona;
        $primero  = $juan->contactosEmergencia()->orderBy('id')->first();

        $this->patch(route('llegada.datos', $juan), [
            'nombres'         => 'Juan Carlos',
            'apellidos'       => $persona->apellidos,
            'cedula'          => $persona->cedula,
            'fechaNacimiento' => '1990-05-10',
            'celular'         => '310 555 1234',
            'ciudad'          => 'Jamundí',
            'barrio'          => 'Centro',
            'padrino'         => 'Nuevo Padrino',
            'padrinoTelefono' => '3159998877',
            'contactos'       => [
                ['nombre' => 'Rosa Corregida', 'parentesco' => 'Esposa', 'celular' => '3001112233'],
                ['nombre' => 'Luis Corregido', 'parentesco' => 'Hijo', 'celular' => '3004445566'],
            ],
        ])->assertSessionHas('mensajeActualizado');

        $this->assertSame('Juan Carlos', $persona->refresh()->nombres);
        $this->assertSame('3105551234', $persona->celular);
        $this->assertSame('3159998877', $juan->refresh()->padrino_telefono);

        $primero->refresh();
        $this->assertSame('Rosa Corregida', $primero->nombre);
        $this->assertSame('6025555555', $primero->telefono_fijo, 'El fijo que dio al inscribirse no se pierde');
        $this->assertSame(2, $juan->contactosEmergencia()->count());
    }



    public function test_membretes_y_oraciones_salen_uno_por_invitado_con_el_numero_del_retiro(): void
    {
        $this->inscrito('Masculino');
        $this->inscrito('Masculino');
        $this->inscrito('Masculino', ['inscripcion' => ['estado' => Inscripcion::NO_ASISTIO]]);

        $membretes = $this->get(route('impresiones.membretes', ['sexo' => 'hombres']))->assertOk();
        $this->assertSame(2, substr_count($membretes->getContent(), 'RETIRO 90'), 'Uno por invitado activo');

        $oraciones = $this->get(route('impresiones.oraciones', ['sexo' => 'hombres', 'cantidad' => 3]))->assertOk();
        $this->assertSame(3, substr_count($oraciones->getContent(), 'class="oracion-copia"'));
        $oraciones->assertSee('COLEGIO NUESTRA SEÑORA DE CHIQUINQUIRÁ');
    }



    public function test_la_carta_va_solo_a_quien_termino_con_la_fecha_del_ultimo_dia(): void
    {
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Termino']], 1, 1);
        $this->inscrito('Masculino', ['persona' => ['nombres' => 'Seefue'], 'inscripcion' => ['estado' => Inscripcion::ABANDONO]], 1, 2);

        $fecha = $this->retiro->fecha_fin->locale('es')->translatedFormat('j \d\e F \d\e Y');

        $this->get(route('impresiones.carta', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee('TERMINO')
            ->assertDontSee('SEEFUE')
            ->assertSee('Cali, ' . $fecha)
            ->assertSee('Falta crear el próximo retiro'); // no hay próximo: avisa antes de imprimir
    }



    public function test_la_carta_anuncia_sola_el_proximo_retiro(): void
    {
        Retiro::create($this->retiro->only(['ofrenda', 'porcentaje_reserva', 'lugar', 'ciudad', 'hora_inicio', 'hora_fin']) + [
            'numero_hombres' => 91, 'numero_mujeres' => 90,
            'fecha_inicio' => now()->addMonths(3)->toDateString(), 'fecha_fin' => now()->addMonths(3)->addDays(2)->toDateString(),
            'inscripciones_abiertas' => false,
        ]);
        $this->inscrito('Masculino', [], 1, 1);

        $this->get(route('impresiones.carta', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee('Nuestro próximo retiro de conversión simultáneo será los días')
            ->assertDontSee('Falta crear el próximo retiro');
    }



    public function test_entrega_de_previstos_e_inventario_dicen_el_retiro(): void
    {
        $this->get(route('impresiones.previstos', ['sexo' => 'mujeres']))
            ->assertOk()->assertSee('Retiro N.º 89 Mujeres')->assertSee('Falda');

        $this->get(route('impresiones.previstos', ['sexo' => 'hombres']))
            ->assertOk()->assertDontSee('Falda');

        $this->get(route('inventario.imprimirTodos'))->assertOk();
    }



    public function test_secretaria_puede_imprimir_todo(): void
    {
        $this->inscrito('Masculino', [], 1, 1);
        $rutas = [
            'impresiones.index', 'impresiones.llegada', 'impresiones.llamado', 'impresiones.nombresMesa', 'impresiones.mesasEnBlanco',
            'impresiones.correcciones', 'impresiones.carta', 'impresiones.directorio', 'impresiones.ubicacion', 'impresiones.camisetas',
            'impresiones.sobres', 'impresiones.llamadas', 'impresiones.puertas', 'impresiones.familiares', 'impresiones.catequistas',
            'impresiones.membretes', 'impresiones.oraciones', 'impresiones.previstos', 'impresiones.flores', 'impresiones.letrerosMesa',
            'pagos.imprimir', 'inventario.imprimirTodos', 'letreros.index', 'cronograma.imprimir',
        ];

        foreach ($rutas as $ruta) {
            $this->get(route($ruta))->assertOk();
        }

        foreach (['rector', 'vicerrector', 'dirigente'] as $cargo) {
            $this->get(route('impresiones.carpeta', $cargo))->assertOk();
        }
    }



    public function test_todos_los_letreros_abren_con_el_numero_del_retiro(): void
    {
        foreach (array_keys(\App\Http\Controllers\LetreroController::LETREROS) as $clave) {
            $this->get(route('letreros.show', $clave))->assertOk();
        }

        $this->get(route('letreros.show', 'ayudasDeDios'))->assertSee('R. 89 M – 90 H');

        // Letreros de pared: la letra baja según lo largo, para no salirse ni partir palabras
        $this->get(route('impresiones.letrerosMesa', ['sexo' => 'hombres', 'mesas' => '12']))->assertOk()->assertSee('Mesa 12');
        $this->get(route('impresiones.letrerosMesa', ['lugar' => 'Rectorado']))->assertOk()->assertSee('letrero-mediano', false);
        $this->get(route('impresiones.letrerosMesa', ['lugar' => 'Coordinación general']))->assertOk()->assertSee('letrero-largo', false);
    }



    public function test_directivos_imprime_desarrollo_y_directorio_pero_no_lo_de_secretaria(): void
    {
        $this->actingAs($this->usuario(Rol::DIRECTIVOS));
        $this->inscrito('Masculino', [], 1, 1);

        $this->get(route('directivos.index'))->assertOk()->assertSee('Imprimir directorio de hombres');
        $this->get(route('directivos.directorio', ['sexo' => 'hombres']))->assertOk()->assertSee('Retiro 90')->assertSee(route('directivos.index'));
        // Entra al desarrollo (aquí no está preparado, así que vuelve a su página con el aviso, no al inicio)
        $this->get(route('desarrollo.imprimir', ['sexo' => 'hombres']))->assertRedirect(route('desarrollo.index'))->assertSessionHas('error');
        $this->get(route('desarrollo.index'))->assertOk()->assertDontSee(route('desarrollo.editar', ['sexo' => 'hombres']));

        $this->get(route('impresiones.carta'))->assertRedirect();
        $this->get(route('impresiones.directorio'))->assertRedirect();
    }



    public function test_inventario_dice_1_unidad_y_el_cronograma_no_repite_el_dia(): void
    {
        $this->assertSame('1 unidad', \App\Models\Articulo::cantidadConUnidad(1, 'unidades'));
        $this->assertSame('1 kilo', \App\Models\Articulo::cantidadConUnidad(1, 'kilos'));
        $this->assertSame('3 paquetes', \App\Models\Articulo::cantidadConUnidad(3, 'paquetes'));

        \App\Models\TareaCronograma::create(['dia_relativo' => 0, 'hora' => '14:00', 'descripcion' => 'Recibir a los invitados', 'responsable' => 'Secretaría']);

        $this->get(route('cronograma.imprimir'))->assertOk()
            ->assertSee(\Illuminate\Support\Str::ucfirst($this->retiro->fecha_inicio->locale('es')->translatedFormat('l j \d\e F')))
            ->assertDontSee('Viernes · viernes');
    }



    public function test_tesoreria_no_entra_a_impresiones_de_secretaria(): void
    {
        $this->actingAs($this->usuario(Rol::TESORERIA));

        $this->get(route('pagos.imprimir'))->assertOk();
        $this->get(route('impresiones.carta'))->assertRedirect();
    }
}
