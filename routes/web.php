<?php

use App\Http\Controllers\AjusteController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CronogramaController;
use App\Http\Controllers\DesarrolloController;
use App\Http\Controllers\DirectivoController;
use App\Http\Controllers\ExportacionController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ImpresionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\InscritoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LetreroController;
use App\Http\Controllers\LlegadaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RetiroController;
use App\Http\Controllers\SantisimoController;
use App\Http\Controllers\SantisimoRepartoController;
use App\Http\Controllers\UsuarioController;
use App\Models\Rol;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
-------------------------------------------------------------------
*/

Route::get('/', [InicioController::class, 'index'])->name('inicio');
Route::get('/inscripcion', [InscripcionController::class, 'create'])->name('inscripcion');
Route::post('/inscripcion', [InscripcionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('inscripcion.store');
Route::get('/inscripcion/recibida', [InscripcionController::class, 'recibida'])->name('inscripcion.recibida');
Route::get('/inscripcion/verificar-cedula/{cedula}', [InscripcionController::class, 'verificarCedula'])
    ->middleware('throttle:20,1')
    ->name('inscripcion.verificarCedula');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('web.login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Rutas autenticadas
-------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    //  Todas las cuentas: cada una ve solo el inventario de su equipo
    // (el controlador lo revisa); Administración y Secretaría ven todos.
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/imprimir-todos', [InventarioController::class, 'imprimirTodos'])
        ->middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::SECRETARIA)
        ->name('inventario.imprimirTodos');
    Route::get('/inventario/{equipo}/imprimir', [InventarioController::class, 'imprimir'])->name('inventario.imprimir');
    Route::get('/inventario/{equipo}', [InventarioController::class, 'show'])->name('inventario.show');
    Route::post('/inventario/{equipo}/articulos', [InventarioController::class, 'store'])->name('inventario.store');
    Route::put('/articulos/{articulo}', [InventarioController::class, 'update'])->name('inventario.update');
    Route::post('/articulos/{articulo}/movimientos', [InventarioController::class, 'movimiento'])->name('inventario.movimiento');

    // El horario del Santísimo lo ven todas las cuentas
    Route::get('/santisimo', [SantisimoController::class, 'index'])->name('santisimo.index');

    // Marcar tareas del cronograma en el inicio
    Route::post('/cronograma/{tarea}/marcar', [CronogramaController::class, 'marcar'])
        ->middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::SECRETARIA . ',' . Rol::DIRECTIVOS)
        ->name('cronograma.marcar');

    // La lista de tareas solo la editan Administración y Secretaría
    Route::resource('cronograma', CronogramaController::class)
        ->parameters(['cronograma' => 'tarea'])
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::SECRETARIA);


    //  Directivos: la lista de inscritos y el desarrollo del retiro, solo
    // para consultar. Administración y Secretaría también ven el desarrollo.
    Route::get('/directivos', [DirectivoController::class, 'index'])
        ->middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::DIRECTIVOS)
        ->name('directivos.index');
    Route::get('/desarrollo', [DesarrolloController::class, 'index'])
        ->middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::SECRETARIA . ',' . Rol::DIRECTIVOS)
        ->name('desarrollo.index');


    /*
|--------------------------------------------------------------------------
| Solo Administración: crear y editar cuentas
-------------------------------------------------------------------
*/
    Route::middleware('rol:' . Rol::ADMINISTRACION)->group(function () {
        Route::resource('usuarios', UsuarioController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });


    /*
|--------------------------------------------------------------------------
| Solo Administración y Secretaría
-------------------------------------------------------------------
*/
    Route::middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::SECRETARIA)->group(function () {
        Route::resource('retiros', RetiroController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::resource('inscritos', InscritoController::class)
            ->parameters(['inscritos' => 'inscripcion'])
            ->only(['edit', 'update']);
        Route::patch('/inscritos/{inscripcion}/estado', [InscritoController::class, 'cambiarEstado'])
            ->name('inscritos.estado');
        Route::post('/inscritos/{inscripcion}/confirmacion', [InscritoController::class, 'enviarConfirmacion'])
            ->name('inscritos.confirmacion');

        //  Las mesas se crean solas al escribir el puesto de cada persona,
        // o a mano aquí cuando hace falta una más.
        Route::resource('mesas', MesaController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/llegada', [LlegadaController::class, 'index'])->name('llegada.index');
        Route::post('/llegada/{inscripcion}', [LlegadaController::class, 'marcar'])->name('llegada.marcar');
        Route::patch('/llegada/{inscripcion}/puesto', [LlegadaController::class, 'asignarPuesto'])->name('llegada.puesto');
        Route::patch('/llegada/{inscripcion}/deshacer', [LlegadaController::class, 'deshacer'])->name('llegada.deshacer');
        Route::patch('/llegada/{inscripcion}/datos', [LlegadaController::class, 'corregirDatos'])->name('llegada.datos');

        Route::resource('archivos', ArchivoController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);

        Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
        Route::post('/historial/importar', [HistorialController::class, 'importar'])->name('historial.importar');

        //  Enlace del grupo de WhatsApp y correo de confirmación
        Route::get('/ajustes', [AjusteController::class, 'index'])->name('ajustes.index');
        Route::put('/ajustes', [AjusteController::class, 'update'])->name('ajustes.update');
        Route::get('/ajustes/correo-de-prueba', [AjusteController::class, 'vistaPrevia'])->name('ajustes.vistaPrevia');
        Route::get('/ajustes/qr', [AjusteController::class, 'qr'])->name('ajustes.qr');

        //  Desarrollo del retiro (actividades y charlas por sexo): es para
        // el papeleo de Secretaría.
        Route::get('/desarrollo/editar', [DesarrolloController::class, 'editar'])->name('desarrollo.editar');
        Route::put('/desarrollo', [DesarrolloController::class, 'guardar'])->name('desarrollo.guardar');
        Route::post('/desarrollo/actividades', [DesarrolloController::class, 'agregar'])->name('desarrollo.agregar');
        Route::post('/desarrollo/copiar-base', [DesarrolloController::class, 'copiarBase'])->name('desarrollo.copiarBase');
        Route::get('/desarrollo/imprimir', [DesarrolloController::class, 'imprimir'])->name('desarrollo.imprimir');
        Route::get('/desarrollo/base', [DesarrolloController::class, 'base'])->name('desarrollo.base');
        Route::put('/desarrollo/base', [DesarrolloController::class, 'guardarBase'])->name('desarrollo.guardarBase');
        Route::post('/desarrollo/base/actividades', [DesarrolloController::class, 'agregarBase'])->name('desarrollo.agregarBase');

        //  El horario base se copia a cada retiro; la copia se edita sin
        // tocar la base.
        Route::get('/santisimo/editar', [SantisimoController::class, 'editar'])->name('santisimo.editar');
        Route::put('/santisimo', [SantisimoController::class, 'guardar'])->name('santisimo.guardar');
        Route::post('/santisimo/copiar-base', [SantisimoController::class, 'copiarBase'])->name('santisimo.copiarBase');
        Route::get('/santisimo/imprimir', [SantisimoController::class, 'imprimir'])->name('santisimo.imprimir');
        Route::get('/santisimo/base', [SantisimoController::class, 'base'])->name('santisimo.base');
        Route::put('/santisimo/base', [SantisimoController::class, 'guardarBase'])->name('santisimo.guardarBase');
        Route::post('/santisimo/base/franjas', [SantisimoController::class, 'agregarBase'])->name('santisimo.agregarBase');
        Route::delete('/santisimo/base/franjas/{franja}', [SantisimoController::class, 'quitarBase'])->name('santisimo.quitarBase');

        //  Reparto automático: tamaño de los equipos, horas fijas o
        // prohibidas, y repartir la base o la copia del retiro.
        Route::get('/santisimo/reparto', [SantisimoRepartoController::class, 'index'])->name('santisimo.reparto');
        Route::put('/santisimo/reparto/equipos', [SantisimoRepartoController::class, 'guardarEquipos'])->name('santisimo.reparto.equipos');
        Route::post('/santisimo/reparto/reglas', [SantisimoRepartoController::class, 'store'])->name('santisimo.reparto.store');
        Route::delete('/santisimo/reparto/reglas/{regla}', [SantisimoRepartoController::class, 'destroy'])->name('santisimo.reparto.destroy');
        Route::post('/santisimo/reparto', [SantisimoRepartoController::class, 'repartir'])->name('santisimo.reparto.repartir');

        //  Letreros para la pared, con vista previa antes de imprimir
        Route::get('/letreros', [LetreroController::class, 'index'])->name('letreros.index');
        Route::get('/letreros/{clave}', [LetreroController::class, 'show'])->name('letreros.show');

        Route::get('/impresiones', [ImpresionController::class, 'index'])->name('impresiones.index');
        Route::get('/impresiones/datos', [ImpresionController::class, 'datos'])->name('impresiones.datos');
        Route::put('/impresiones/rectorado', [ImpresionController::class, 'guardarRectorado'])->name('impresiones.rectorado');
        Route::put('/impresiones/firmantes', [ImpresionController::class, 'guardarFirmantes'])->name('impresiones.firmantes');
        Route::put('/impresiones/perseverancias', [ImpresionController::class, 'guardarPerseverancias'])->name('impresiones.perseverancias');
        Route::get('/impresiones/carta', [ImpresionController::class, 'carta'])->name('impresiones.carta');
        Route::get('/impresiones/correcciones', [ImpresionController::class, 'correcciones'])->name('impresiones.correcciones');
        Route::get('/impresiones/directorio', [ImpresionController::class, 'directorio'])->name('impresiones.directorio');
        Route::get('/impresiones/ubicacion', [ImpresionController::class, 'ubicacion'])->name('impresiones.ubicacion');
        Route::get('/impresiones/llegada', [ImpresionController::class, 'llegada'])->name('impresiones.llegada');
        Route::get('/impresiones/mesas-en-blanco', [ImpresionController::class, 'mesasEnBlanco'])->name('impresiones.mesasEnBlanco');
        Route::get('/impresiones/letreros-mesa', [ImpresionController::class, 'letrerosMesa'])->name('impresiones.letrerosMesa');
        Route::get('/impresiones/camisetas', [ImpresionController::class, 'camisetas'])->name('impresiones.camisetas');
        Route::get('/impresiones/sobres', [ImpresionController::class, 'sobres'])->name('impresiones.sobres');
        Route::get('/impresiones/llamadas', [ImpresionController::class, 'llamadas'])->name('impresiones.llamadas');
        Route::get('/impresiones/familiares', [ImpresionController::class, 'familiares'])->name('impresiones.familiares');
        Route::get('/impresiones/puertas', [ImpresionController::class, 'puertas'])->name('impresiones.puertas');
        Route::get('/impresiones/flores', [ImpresionController::class, 'flores'])->name('impresiones.flores');
        Route::get('/impresiones/catequistas', [ImpresionController::class, 'catequistas'])->name('impresiones.catequistas');
        Route::get('/impresiones/informes/{formato}', [ImpresionController::class, 'informe'])->name('impresiones.informe');
        Route::get('/impresiones/carpetas/{cargo}', [ImpresionController::class, 'carpeta'])->name('impresiones.carpeta');
    });


    /*
|--------------------------------------------------------------------------
| Administración, Secretaría y Tesorería: ver inscritos y pagos
-------------------------------------------------------------------
*/
    Route::middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::SECRETARIA . ',' . Rol::TESORERIA)->group(function () {
        Route::resource('inscritos', InscritoController::class)
            ->parameters(['inscritos' => 'inscripcion'])
            ->only(['index', 'show']);
        Route::get('/inscritos/{inscripcion}/comprobante', [InscritoController::class, 'comprobante'])
            ->name('inscritos.comprobante');

        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/imprimir', [PagoController::class, 'imprimir'])->name('pagos.imprimir');

        // Descargas para Excel: respaldo de inscritos y de pagos del retiro actual
        Route::get('/exportar/inscritos', [ExportacionController::class, 'inscritos'])->name('exportar.inscritos');
        Route::get('/exportar/pagos', [ExportacionController::class, 'pagos'])->name('exportar.pagos');
        Route::get('/pagos/{pago}/comprobante', [PagoController::class, 'comprobante'])->name('pagos.comprobante');
    });


    /*
|--------------------------------------------------------------------------
| Administración y Tesorería: registrar y corregir pagos
-------------------------------------------------------------------
*/
    Route::middleware('rol:' . Rol::ADMINISTRACION . ',' . Rol::TESORERIA)->group(function () {
        Route::post('/inscritos/{inscripcion}/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::put('/pagos/{pago}', [PagoController::class, 'update'])->name('pagos.update');
    });
});
