<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovimientoInventarioRequest;
use App\Http\Requests\StoreArticuloRequest;
use App\Http\Requests\UpdateArticuloRequest;
use App\Models\Articulo;
use App\Models\Equipo;
use App\Models\MovimientoInventario;
use App\Models\Retiro;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{

    //  Administración y Secretaría eligen el equipo; las demás cuentas
    // van directo al inventario de su propio equipo.
    public function index()
    {
        $usuario = auth()->user();

        if (! $usuario->tieneRol(Rol::ADMINISTRACION, Rol::SECRETARIA)) {
            if (! $usuario->equipo) {
                return view('inventario.index', ['equipos' => collect(), 'sinEquipo' => true]);
            }

            return redirect()->route('inventario.show', $usuario->equipo);
        }

        $equipos = Equipo::withCount('articulos')
            ->withSum('articulos', 'por_comprar')
            ->orderBy('nombre')
            ->get();

        return view('inventario.index', ['equipos' => $equipos, 'sinEquipo' => false]);
    }



    public function store(StoreArticuloRequest $request, Equipo $equipo)
    {
        $this->revisarAcceso($equipo);

        $data = $request->validated();

        DB::transaction(function () use ($data, $equipo) {
            $articulo = $equipo->articulos()->create($data);

            // La cantidad inicial queda como la primera entrada del historial
            if ($articulo->cantidad > 0) {
                $articulo->movimientos()->create([
                    'tipo'                => MovimientoInventario::ENTRADA,
                    'cantidad'            => $articulo->cantidad,
                    'cantidad_resultante' => $articulo->cantidad,
                    'nota'                => 'Cantidad inicial',
                    'usuario_id'          => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventario.show', $equipo)->with('mensajeCreado', 'Artículo agregado.');
    }



    public function show(Equipo $equipo)
    {
        $this->revisarAcceso($equipo);

        $articulos = $equipo->articulos()
            ->with(['movimientos' => fn ($consulta) => $consulta->latest()->limit(200), 'movimientos.usuario'])
            ->orderBy('nombre')
            ->get();

        $porComprar = $articulos->where('por_comprar', '>', 0)->count();

        return view('inventario.equipo', compact('equipo', 'articulos', 'porComprar'));
    }



    //  Hoja del inventario para el conteo al final del retiro (Secretaría
    // entrega copia al dirigente diocesano). Con espacio para el conteo
    // físico y las firmas. Administración y Secretaría pueden sacar la
    // de todos los equipos juntos.
    public function imprimir(Equipo $equipo)
    {
        $this->revisarAcceso($equipo);

        return view('inventario.imprimir', [
            'equipos' => collect([$equipo->load(['articulos' => fn ($consulta) => $consulta->orderBy('nombre')])]),
            'titulo'  => 'Inventario · ' . $equipo->nombre,
            'retiro'  => Retiro::actual(),
            'volver'  => route('inventario.show', $equipo),
        ]);
    }



    public function imprimirTodos()
    {
        $equipos = Equipo::with(['articulos' => fn ($consulta) => $consulta->orderBy('nombre')])
            ->has('articulos')
            ->orderBy('nombre')
            ->get();

        return view('inventario.imprimir', [
            'equipos' => $equipos,
            'titulo'  => 'Inventario de todos los equipos',
            'retiro'  => Retiro::actual(),
            'volver'  => route('inventario.index'),
        ]);
    }



    public function update(UpdateArticuloRequest $request, Articulo $articulo)
    {
        $this->revisarAcceso($articulo->equipo);

        // Cargar datos sin guardar
        $articulo->fill($request->validated());

        // No hubo cambios
        if (! $articulo->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $articulo->save();

        return back()->with('mensajeActualizado', $articulo->nombre . ' actualizado.');
    }



    //  Entrada suma, salida resta y ajuste deja la cantidad exacta
    // (para cuando se cuenta el inventario y no cuadra).
    public function movimiento(MovimientoInventarioRequest $request, Articulo $articulo)
    {
        $this->revisarAcceso($articulo->equipo);

        $data = $request->validated();

        $resultado = DB::transaction(function () use ($data, $articulo) {

            // lockForUpdate: dos personas del equipo moviendo el mismo artículo a la vez
            $articulo = Articulo::lockForUpdate()->find($articulo->id);

            $nueva = match ($data['tipo']) {
                MovimientoInventario::ENTRADA => $articulo->cantidad + $data['cantidad'],
                MovimientoInventario::SALIDA  => $articulo->cantidad - $data['cantidad'],
                default                       => (int) $data['cantidad'],
            };

            if ($nueva < 0) {
                return 'No se pueden sacar ' . $data['cantidad'] . ': solo hay ' . $articulo->cantidad . ' ' . $articulo->unidad . '.';
            }

            if ($data['tipo'] === MovimientoInventario::AJUSTE && $nueva === $articulo->cantidad) {
                return 'La cantidad ya era ' . $nueva . '. No se registró ningún cambio.';
            }

            $articulo->update(['cantidad' => $nueva]);
            $articulo->movimientos()->create([
                'tipo'                => $data['tipo'],
                'cantidad'            => $data['cantidad'],
                'cantidad_resultante' => $nueva,
                'nota'                => $data['nota'] ?? null,
                'usuario_id'          => auth()->id(),
            ]);

            return $articulo;
        });

        if (is_string($resultado)) {
            return back()->with('error', $resultado);
        }

        $verbo = ['entrada' => 'Entrada registrada', 'salida' => 'Salida registrada', 'ajuste' => 'Ajuste registrado'][$data['tipo']];

        return back()->with('mensajeActualizado', $verbo . ': ' . $resultado->nombre . ' queda en ' . $resultado->cantidad . ' ' . $resultado->unidad . '.');
    }



    //  abort() con una respuesta corta la petición ahí mismo y devuelve
    // a la persona a su inventario, en vez de la página genérica de error.
    private function revisarAcceso(Equipo $equipo): void
    {
        $usuario = auth()->user();

        if (! $usuario->puedeVerInventario($equipo)) {
            $destino = $usuario->equipo ? route('inventario.show', $usuario->equipo) : route('panel');
            abort(redirect($destino)->with('mensajeAlerta', 'Solo puedes ver el inventario de tu equipo.'));
        }
    }
}
