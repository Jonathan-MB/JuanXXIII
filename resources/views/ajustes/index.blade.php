@php
    use App\Models\Ajuste;
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/ajustes.css') }}">
<title>Ajustes · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">Ajustes</h1>
    @include('partials.mensaje')

    <form class="formulario-ajustes" method="POST" action="{{ route('ajustes.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- --------------------WHATSAPP------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Grupo de WhatsApp</legend>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="whatsappEnlace">Enlace para unirse al grupo</label>
                <input type="url" name="whatsappEnlace" id="whatsappEnlace" maxlength="255" placeholder="https://chat.whatsapp.com/..." value="{{ old('whatsappEnlace', $ajustes[Ajuste::WHATSAPP]) }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="qrWhatsapp">Imagen del código QR <small>(PNG, JPG o WEBP; máximo 2 MB)</small></label>
                <input type="file" name="qr" id="qrWhatsapp" accept="image/png,image/jpeg,image/webp">
            </div>
            @if ($tieneQr)
                <div class="qr-actual">
                    <img src="{{ route('ajustes.qr') }}" alt="Código QR del grupo de WhatsApp">
                    <label class="casilla-ajuste">
                        <input type="checkbox" name="quitarQr" value="1">
                        Quitar la imagen
                    </label>
                </div>
            @endif
            <p class="texto-ayuda">El enlace y el QR salen en el directorio, al pie de cada hoja. Lo que se deje vacío no sale. Una imagen nueva reemplaza la anterior.</p>
        </fieldset>

        {{-- --------------------ROSARIO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Rosario en la página pública</legend>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="rosarioEnlace">Enlace de Google Meet</label>
                <input type="url" name="rosarioEnlace" id="rosarioEnlace" maxlength="255" placeholder="https://meet.google.com/..." value="{{ old('rosarioEnlace', $ajustes[Ajuste::ROSARIO_ENLACE]) }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="rosarioDias">Días</label>
                <input type="text" name="rosarioDias" id="rosarioDias" maxlength="60" placeholder="Lunes a sábado" value="{{ old('rosarioDias', $ajustes[Ajuste::ROSARIO_DIAS]) }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="rosarioHora">Hora</label>
                <input type="text" name="rosarioHora" id="rosarioHora" maxlength="30" placeholder="5:30 a. m." value="{{ old('rosarioHora', $ajustes[Ajuste::ROSARIO_HORA]) }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="rosarioPedidos">WhatsApp para pedidos de oración</label>
                <input type="tel" inputmode="numeric" data-solo-numeros name="rosarioPedidos" id="rosarioPedidos" maxlength="20" placeholder="3136441569" value="{{ old('rosarioPedidos', $ajustes[Ajuste::ROSARIO_PEDIDOS]) }}">
            </div>
            <p class="texto-ayuda">Salen en el inicio: el botón «Rosario» arriba y la invitación con el horario abajo. Si borras el enlace, el rosario deja de salir.</p>
        </fieldset>

        {{-- --------------------LETRERO DE NEQUI------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Letrero de Nequi</legend>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="nequiNumero">Número de Nequi</label>
                <input type="tel" inputmode="numeric" data-solo-numeros name="nequiNumero" id="nequiNumero" maxlength="20" value="{{ old('nequiNumero', $ajustes[Ajuste::NEQUI_NUMERO]) }}" placeholder="{{ $cuentaNequi?->numero ?? 'Ej.: 3155724732' }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="nequiTitular">A nombre de</label>
                <input type="text" name="nequiTitular" id="nequiTitular" maxlength="100" value="{{ old('nequiTitular', $ajustes[Ajuste::NEQUI_TITULAR]) }}" placeholder="{{ $cuentaNequi?->titular ?? 'Quien recibe el pago' }}">
            </div>
            <p class="texto-ayuda">Salen en los letreros de pago de Librería, Cafetería y Floristería. Si se dejan vacíos, se usa la cuenta Nequi del retiro{{ $cuentaNequi ? ' (' . $cuentaNequi->numero . ' · ' . $cuentaNequi->titular . ')' : '' }}.</p>
        </fieldset>

        {{-- --------------------CORREO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Correo de confirmación de cupo</legend>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="correoAsunto">Asunto</label>
                <input type="text" name="correoAsunto" id="correoAsunto" maxlength="150" value="{{ old('correoAsunto', $ajustes[Ajuste::CORREO_ASUNTO]) }}" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="correoTexto">Texto</label>
                <textarea name="correoTexto" id="correoTexto" rows="12" maxlength="3000" required>{{ old('correoTexto', $ajustes[Ajuste::CORREO_TEXTO]) }}</textarea>
            </div>
            <p class="texto-ayuda">
                Estas palabras se cambian por los datos de cada persona:
                <code>{nombre}</code> <code>{retiro}</code> <code>{fechas}</code> <code>{lugar}</code> <code>{direccion}</code> <code>{hora}</code>.
                Una línea en blanco separa los párrafos.
            </p>

            <label class="casilla-ajuste">
                <input type="checkbox" name="correoAutomatico" value="1" {{ old('correoAutomatico', $ajustes[Ajuste::CORREO_AUTOMATICO]) === '1' ? 'checked' : '' }}>
                Enviarlo apenas la persona se inscribe
            </label>
            <p class="texto-ayuda">Si no se marca, se envía a mano desde la ficha de cada inscrito, con el botón «Enviar correo».</p>

            @if ($hayEjemplo)
                <p><a class="enlace-accion" href="{{ route('ajustes.vistaPrevia') }}" target="_blank" rel="noopener">Ver cómo queda el correo (con lo que está guardado) →</a></p>
            @endif

            @unless ($correoActivo)
                <p class="aviso-ajuste">El servidor todavía no tiene una cuenta de correo configurada: por ahora los correos no salen, solo quedan anotados en el registro. Se configura en el archivo .env (ver DESPLIEGUE.md).</p>
            @endunless
        </fieldset>

        <button type="submit" class="boton-amarillo">Guardar ajustes</button>
    </form>

@include('partials.footerPanel')
