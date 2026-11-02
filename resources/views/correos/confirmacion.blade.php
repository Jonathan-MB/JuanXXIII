<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de tu cupo</title>
</head>
{{-- Los correos no leen hojas de estilo: todo va en línea --}}
<body style="margin:0; padding:0; background:#f2f4f8; font-family:Arial, Helvetica, sans-serif; color:#1f2533;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f8; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#1c2f5e; padding:18px 24px; color:#ffffff;">
                            <p style="margin:0; font-size:13px; letter-spacing:1px; color:#f4c20d; font-weight:bold;">MOVIMIENTO DE RETIROS PARROQUIALES JUAN XXIII</p>
                            <p style="margin:4px 0 0; font-size:12px; color:#c9d1e3;">Arquidiócesis de Cali</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px; font-size:15px; line-height:1.6;">
                            @foreach ($parrafos as $parrafo)
                                <p style="margin:0 0 14px;">{!! nl2br(e($parrafo)) !!}</p>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px; background:#f7f8fc; font-size:12px; color:#5b6479;">
                            Con Cristo TODO · Sin Cristo NADA
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
