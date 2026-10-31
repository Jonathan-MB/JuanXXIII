<?php

//  Igual que asset(), pero le agrega la fecha de modificación del archivo
// (?v=...). Así, cuando se sube un CSS o JS nuevo, el navegador lo vuelve
// a pedir en vez de seguir usando el que tenía guardado.
if (! function_exists('recurso')) {
    function recurso(string $ruta): string
    {
        $archivo = public_path($ruta);

        return asset($ruta) . (is_file($archivo) ? '?v=' . filemtime($archivo) : '');
    }
}
