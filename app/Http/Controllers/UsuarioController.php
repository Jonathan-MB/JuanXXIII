<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Equipo;
use App\Models\Rol;
use App\Models\Usuario;

class UsuarioController extends Controller
{

    public function index()
    {
        $usuarios = Usuario::with(['rol', 'equipo'])
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }



    public function create()
    {
        $usuario = new Usuario(['activo' => true]);

        return view('usuarios.formulario', $this->datosFormulario($usuario));
    }



    public function store(StoreUsuarioRequest $request)
    {
        Usuario::create($request->validated());

        return redirect()->route('usuarios.index')->with('mensajeCreado', 'Cuenta creada.');
    }



    public function edit(Usuario $usuario)
    {
        return view('usuarios.formulario', $this->datosFormulario($usuario));
    }



    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return back()->with('mensajeAlerta', 'Sin datos');
        }

        //  La contraseña vacía significa "no cambiarla". Si se dejara
        // pasar, el modelo hashearía el texto vacío y la cuenta no entraría.
        if (empty($data['contrasena'])) {
            unset($data['contrasena']);
        }

        //  Quien edita su propia cuenta no puede quitarse el rol de
        // administración ni desactivarse: se quedaría sin poder entrar.
        if ($usuario->is(auth()->user())) {
            $rolAdministracion = Rol::where('nombre', Rol::ADMINISTRACION)->value('id');

            if ((isset($data['activo']) && ! $data['activo']) || (isset($data['rol_id']) && (int) $data['rol_id'] !== $rolAdministracion)) {
                return back()->with('error', 'No puedes desactivar tu propia cuenta ni quitarte el rol de administración.');
            }
        }

        // Cargar datos sin guardar
        $usuario->fill($data);

        // No hubo cambios
        if (! $usuario->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('mensajeActualizado', 'Cuenta actualizada.');
    }



    //  Se manda como $cuenta y no como $usuario, porque el header ya usa
    // $usuario para la persona que tiene la sesión abierta.
    private function datosFormulario(Usuario $usuario): array
    {
        return [
            'cuenta'  => $usuario,
            'roles'   => Rol::orderBy('id')->get(),
            'equipos' => Equipo::orderBy('nombre')->get(),
        ];
    }
}
