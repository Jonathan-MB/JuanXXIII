    @if (session('mensajeCreado'))
        <div class="mensaje" id="mensaje">
            <p>{{ session('mensajeCreado') }}</p>
        </div>
    @endif


    @if (session('mensajeActualizado'))
        <div class="mensaje" id="mensaje">
            <p>{{ session('mensajeActualizado') }}</p>
        </div>
    @endif

    @if (session('mensajeAlerta'))
        <div class="mensaje mensaje-alerta" id="mensaje">
            <p>{{ session('mensajeAlerta') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mensaje mensaje-error" id="mensaje">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mensaje mensaje-error" id="mensaje-errores">
            <p>Revisa estos datos:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
