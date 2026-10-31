<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'usuario'  => 'required|string|max:45',
            'password' => 'required',
        ], [
            'usuario.required'  => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        //  "activo" va dentro de las credenciales: una cuenta desactivada
        // no entra aunque la contraseña sea correcta.
        $credenciales = [
            'usuario'  => trim($request->usuario),
            'password' => $request->password,
            'activo'   => true,
        ];

        // Con "Recordarme" la sesión dura 3 días (config/auth.php)
        if (! Auth::attempt($credenciales, $request->boolean('recordarme'))) {
            return back()
                ->withErrors(['usuario' => 'Usuario o contraseña incorrecta, o la cuenta no está activa.'])
                ->withInput($request->only('usuario'));
        }

        $request->session()->regenerate();

        return redirect()->route('panel');
    }



    public function logout(Request $request)
    {

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }
}
