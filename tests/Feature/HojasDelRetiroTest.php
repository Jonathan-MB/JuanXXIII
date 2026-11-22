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



    public function test_la_lista_sencilla_va_por_mesa_y_silla_con_directivos(): void
    {
        $juan  = $this->inscrito('Masculino', [], 2, 5);
        $maria = $this->inscrito('Femenino', [], 1, 3);
        $this->retiro->rectorados()->create(['sexo' => 'Masculino', 'cargo' => 'Rector(a)', 'nombre' => 'Rector Inventado', 'orden' => 1]);

        $this->get(route('impresiones.sencilla'))
            ->assertOk()
            ->assertSeeInOrder(['Lista sencilla · Retiro N.º 90 Hombres', 'Directivos', 'Rector Inventado', 'Mesa 2', 'CT Mesa 2', 'CA Mesa 2', '>5<', $juan->persona->nombre_completo, 'Retiro N.º 89 Mujeres', 'Mesa 1', $maria->persona->nombre_completo], false);

        //  Sin mesa y silla no vino: solo sale en llamado y tesorería
        $sinPuesto = $this->inscrito('Masculino', ['persona' => ['nombres' => 'Nunca', 'apellidos' => 'Llegó', 'fecha_nacimiento' => '1990-' . $this->retiro->fecha_inicio->format('m-d')]]);
        $this->get(route('impresiones.sencilla'))->assertDontSee('Nunca Llegó');
        $this->get(route('impresiones.cumpleanos'))->assertDontSee('Nunca Llegó');
        $this->get(route('impresiones.llamado'))->assertSee('Nunca Llegó');

        $this->get(route('impresiones.sencilla', ['sexo' => 'mujeres']))
            ->assertOk()->assertDontSee($juan->persona->nombre_completo);
    }



    public function test_la_hoja_para_cartas_sale_con_y_sin_renglones(): void
    {
        $this->get(route('impresiones.index'))->assertOk()->assertSee('Hoja para cartas');
        $this->get(route('impresiones.hojaCarta'))->assertOk()->assertSee('hoja-carta-doble con-renglones', false)->assertSee('corte-carta', false)->assertSee('marca-agua-logo', false);
        $this->get(route('impresiones.hojaCarta', ['renglones' => 0]))->assertOk()->assertDontSee('con-renglones', false);
    }



    public function test_hoja_3_trae_los_datos_y_los_contactos(): void
    {
        $juan = $this->inscrito('Masculino', [], 1, 1);

        $this->get(route('impresiones.correcciones', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee($juan->persona->cedula)
            ->assertSee($juan->persona->barrio)
            ->assertSee($juan->contactosEmergencia[0]->nombre)
            ->assertSee('<th class="columna-talla">Talla</th>', false)
            ->assertSee('CATEQUISTA: POR FAVOR VERIFICAR QUE USTED Y TODOS LOS DE SU MESA LLENEN LA INFORMACIÓN COMPLETA')
            ->assertSee('casilla-llenar', false)
            ->assertSee('<td class="columna-talla">' . $juan->persona->talla_camiseta . '</td>', false);
    }



    public function test_la_fila_de_llegada_guarda_datos_contactos_y_puesto_sin_recargar(): void
    {
        $juan    = $this->inscrito('Masculino', [], 1, 1);
        $persona = $juan->persona;
        $primero = $juan->contactosEmergencia()->orderBy('id')->first();

        $this->getJson(route('llegada.index'))->assertOk();
        $this->get(route('llegada.index'))->assertOk()->assertSee('name="contactos[1][celular]"', false)->assertSee('name="tallaCamiseta"', false)->assertSee('Más datos');

        $this->patchJson(route('llegada.datos', $juan), [
            'nombres'         => 'Juan Carlos',
            'apellidos'       => $persona->apellidos,
            'cedula'          => '1.130.456.221',
            'fechaNacimiento' => '1985-05-10',
            'celular'         => '311 222 3344',
            'ciudad'          => 'Jamundí',
            'barrio'          => 'Centro',
            'tallaCamiseta'   => 'XL',
            'contactos'       => [
                ['nombre' => 'Rosa Corregida', 'parentesco' => 'Esposa', 'celular' => '300 111 2233'],
                ['nombre' => 'Luis Corregido', 'parentesco' => 'Hijo', 'celular' => '3004445566'],
            ],
            'mesa'            => 4,
            'silla'           => 2,
        ])->assertOk()->assertJsonPath('puesto', true)->assertJsonStructure(['mensaje', 'edad', 'buscar']);

        $persona->refresh();
        $this->assertSame('Juan Carlos', $persona->nombres);
        $this->assertSame('1130456221', $persona->cedula);
        $this->assertSame('1985-05-10', $persona->fecha_nacimiento->format('Y-m-d'));
        $this->assertSame('3112223344', $persona->celular);
        $this->assertSame('Jamundí', $persona->ciudad);
        $this->assertSame('Centro', $persona->barrio);
        $this->assertSame('XL', $persona->talla_camiseta);
        $this->assertSame(4, $juan->refresh()->mesa->numero);
        $this->assertSame(2, (int) $juan->silla);

        $primero->refresh();
        $this->assertSame('Rosa Corregida', $primero->nombre);
        $this->assertSame('3001112233', $primero->celular);
        $this->assertSame('6025555555', $primero->telefono_fijo, 'El fijo que dio al inscribirse no se pierde');
        $this->assertSame(2, $juan->contactosEmergencia()->count());
    }



    public function test_si_el_puesto_esta_ocupado_la_fila_no_guarda_nada(): void
    {
        $pedro = $this->inscrito('Masculino', [], 1, 1);
        $juan  = $this->inscrito('Masculino', [], 1, 2);

        $this->patchJson(route('llegada.datos', $juan), [
            'nombres'         => 'Otro Nombre',
            'apellidos'       => $juan->persona->apellidos,
            'cedula'          => $juan->persona->cedula,
            'fechaNacimiento' => '1990-05-10',
            'tallaCamiseta'   => 'M',
            'contactos'       => [['nombre' => 'A'], ['nombre' => 'B']],
            'mesa'            => 1,
            'silla'           => 1,
        ])->assertStatus(422)->assertJsonPath('errors.mesa.0', 'La mesa 1, silla 1 ya es de ' . $pedro->persona->nombre_completo . '.');

        $this->assertNotSame('Otro Nombre', $juan->persona->refresh()->nombres);
        $this->assertSame(2, (int) $juan->refresh()->silla);
    }



    public function test_con_mesa_y_silla_llego_y_sin_ellas_falta_por_llegar(): void
    {
        $maria = $this->inscrito('Femenino', ['inscripcion' => ['estado' => \App\Models\Inscripcion::CONFIRMADO, 'llegada_en' => null]]);

        $this->patch(route('llegada.puesto', $maria), ['mesa' => 2, 'silla' => 5])->assertSessionHas('mensajeActualizado');
        $maria->refresh();
        $this->assertSame(\App\Models\Inscripcion::LLEGO, $maria->estado);
        $this->assertNotNull($maria->llegada_en);

        $this->patch(route('llegada.puesto', $maria), ['mesa' => '', 'silla' => '']);
        $maria->refresh();
        $this->assertSame(\App\Models\Inscripcion::CONFIRMADO, $maria->estado);
        $this->assertNull($maria->llegada_en);
    }



    public function test_se_fue_tacha_conserva_la_silla_y_se_puede_deshacer(): void
    {
        $juan = $this->inscrito('Masculino', [], 3, 4);

        $this->patchJson(route('llegada.seFue', $juan))->assertOk()->assertJsonPath('seFue', true);
        $juan->refresh();
        $this->assertSame(\App\Models\Inscripcion::ABANDONO, $juan->estado);
        $this->assertSame(4, (int) $juan->silla, 'Conserva su silla, que queda vacía');

        // Sigue en la lista de Llegada, tachado
        $this->get(route('llegada.index'))->assertOk()->assertSee('fila-se-fue', false)->assertSee($juan->persona->nombres);

        $this->patchJson(route('llegada.seFue', $juan))->assertOk()->assertJsonPath('seFue', false);
        $this->assertSame(\App\Models\Inscripcion::LLEGO, $juan->refresh()->estado);
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



    public function test_sin_proximo_retiro_la_carta_dice_que_se_anunciara(): void
    {
        $this->inscrito('Femenino', [], 1, 1);

        $this->get(route('impresiones.carta', ['sexo' => 'mujeres']))
            ->assertOk()
            ->assertSee('Falta crear el próximo retiro')
            ->assertSee('Estaremos anunciando con anticipación la fecha y el lugar de nuestro próximo retiro')
            ->assertSee('Si la parroquia requiere cupos para vivir esta experiencia')
            ->assertDontSee('espacio-mano', false);
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
            'impresiones.membretes', 'impresiones.oraciones', 'impresiones.sacramentos', 'impresiones.previstos', 'impresiones.flores', 'impresiones.letrerosMesa',
            'pagos.imprimir', 'inventario.imprimirTodos', 'letreros.index', 'cronograma.imprimir',
        ];

        foreach ($rutas as $ruta) {
            $this->get(route($ruta))->assertOk();
        }

        foreach (['rector', 'vicerrector', 'dirigente'] as $cargo) {
            $this->get(route('impresiones.carpeta', $cargo))->assertOk();
        }

        // La carpeta del rector(a) trae la hoja de llamado; la del segundo vicerrector(a), sin evaluación de equipos
        $this->get(route('impresiones.carpeta', 'rector'))->assertSee('Lista de llamado')->assertSee('rotulo-carpeta', false);
        $this->get(route('impresiones.carpeta', ['cargo' => 'vicerrector', 'sexo' => 'hombres']))->assertSee('Evaluación de equipos');
        $this->get(route('impresiones.carpeta', ['cargo' => 'vicerrector', 'sexo' => 'hombres', 'equipos' => 0]))->assertDontSee('Evaluación de equipos · Hombres');
    }



    //  Los datos del formulario antiguo llegaron incompletos: sin EPS, con
    // pasaporte y con contactos a medias. Igual se pueden corregir y guardar.
    public function test_se_pueden_editar_todos_los_datos_aunque_esten_incompletos(): void
    {
        $juan    = $this->inscrito('Masculino', [], 1, 1);
        $persona = $juan->persona;

        $this->get(route('inscritos.edit', ['inscripcion' => $juan, 'volver' => 'llegada']))->assertOk()->assertSee('Volver a Llegada');

        $this->put(route('inscritos.update', $juan), [
            'volver'              => 'llegada',
            'sexo'                => 'Masculino',
            'nombres'             => $persona->nombres,
            'apellidos'           => $persona->apellidos,
            'cedula'              => 'XP123456',
            'fechaNacimiento'     => '1990-05-10',
            'celular'             => '3105551234',
            'correo'              => '',
            'direccion'           => 'Calle 1',
            'barrio'              => 'Centro',
            'ciudad'              => 'Cali',
            'confirmacion'        => '1',
            'tomaMedicamentos'    => '0',
            'eps'                 => '',
            'tallaCamiseta'       => 'XL',
            'contactosEmergencia' => [
                ['nombre' => 'Jaime', 'parentesco' => '', 'celular' => ''],
                ['nombre' => '', 'parentesco' => '', 'celular' => ''],
            ],
            'formaPago'           => 'Efectivo',
            'tienePadrino'        => '0',
        ])->assertRedirect(route('llegada.index'))->assertSessionHas('mensajeActualizado');

        $persona->refresh();
        $this->assertSame('XP123456', $persona->cedula);
        $this->assertSame('', $persona->eps);
        $this->assertSame('XL', $persona->talla_camiseta);
        $this->assertTrue($persona->confirmacion);
        $this->assertSame(['Jaime'], $juan->contactosEmergencia()->pluck('nombre')->all(), 'El contacto vacío no se guarda');

        // Y la corrección rápida de Llegada acepta contactos a medias
        $this->patch(route('llegada.datos', $juan), [
            'nombres'         => $persona->nombres,
            'apellidos'       => $persona->apellidos,
            'cedula'          => 'XP123456',
            'fechaNacimiento' => '1990-05-10',
            'tallaCamiseta'   => 'M',
            'contactos'       => [
                ['nombre' => 'Jaime Pérez', 'parentesco' => '', 'celular' => ''],
                ['nombre' => '', 'parentesco' => '', 'celular' => ''],
            ],
            'mesa'            => 1,
            'silla'           => 1,
        ])->assertSessionHasNoErrors()->assertSessionHas('mensajeActualizado');

        $this->assertSame(['Jaime Pérez'], $juan->contactosEmergencia()->pluck('nombre')->all());
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



    //  La lista para puertas trae el rectorado del grupo y los catequistas de cada mesa
    public function test_la_lista_para_puertas_trae_rectorado_y_catequistas(): void
    {
        $this->inscrito('Masculino', [], 1, 1);
        $this->retiro->rectorados()->create(['sexo' => 'Masculino', 'cargo' => 'Catequista rotativo (CR)', 'nombre' => 'Rotativo Prueba', 'orden' => 4]);

        $this->get(route('impresiones.puertas', ['sexo' => 'hombres']))
            ->assertOk()
            ->assertSee('Directivos')
            ->assertSee('Rotativo Prueba')
            ->assertSee('CT Mesa 1')
            ->assertSee('CA Mesa 1')
            ->assertSee('Catequista titular');
    }
}
