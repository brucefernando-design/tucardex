<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $asunto }}</title>
</head>
<body style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f8fafc; margin:0; padding:20px; color:#1e293b;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width:600px; margin:0 auto; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
            <td style="background-color:#0f172a; padding:24px 28px; text-align:center; border-bottom:4px solid #16a34a;">
                <h2 style="color:#ffffff; margin:0; font-size:18px; text-transform:uppercase; letter-spacing:0.5px;">{{ $schoolName }}</h2>
                <span style="color:#94a3b8; font-size:12px; display:block; margin-top:4px;">Departamento de Secretaría y Control Escolar</span>
            </td>
        </tr>

        <!-- Body Content -->
        <tr>
            <td style="padding:28px 28px 20px 28px;">
                <div style="display:inline-block; background-color:#dcfce7; color:#166534; font-size:11px; font-weight:bold; padding:4px 10px; border-radius:12px; text-transform:uppercase; margin-bottom:16px;">
                    {{ strtoupper($tipo) }} OFICIAL
                </div>

                <p style="font-size:14px; margin-top:0; color:#475569;">
                    Estimado(a): <strong style="color:#0f172a;">{{ $destinatario }}</strong>
                </p>

                <h3 style="color:#0f172a; font-size:16px; margin:16px 0 12px 0;">{{ $asunto }}</h3>

                <div style="font-size:14px; line-height:1.7; color:#334155; white-space:pre-line; background-color:#f8fafc; border-left:4px solid #0f172a; padding:14px 16px; border-radius:4px; margin-bottom:20px;">
{{ $cuerpo }}
                </div>

                @if($fechaCita || $horaCita)
                    <div style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:16px; margin-bottom:20px;">
                        <h4 style="color:#1e40af; margin:0 0 10px 0; font-size:13px; text-transform:uppercase;">Detalles de la Cita / Reunión:</h4>
                        <table style="width:100%; font-size:13px; color:#1e293b;">
                            @if($fechaCita)
                                <tr>
                                    <td style="width:100px; color:#64748b; padding:3px 0;"><strong>Fecha:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($fechaCita)->translatedFormat('l, d \d\e F \d\e Y') }}</td>
                                </tr>
                            @endif
                            @if($horaCita)
                                <tr>
                                    <td style="color:#64748b; padding:3px 0;"><strong>Horario:</strong></td>
                                    <td>{{ $horaCita }} hrs.</td>
                                </tr>
                            @endif
                            @if($lugar)
                                <tr>
                                    <td style="color:#64748b; padding:3px 0;"><strong>Lugar:</strong></td>
                                    <td>{{ $lugar }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                @endif

                <div style="background-color:#f1f5f9; border-radius:6px; padding:12px 14px; font-size:12px; color:#475569; margin-bottom:20px;">
                    📎 <strong>Documento Adjunto:</strong> Se adjunta a este correo electrónico el documento oficial membretado en formato PDF con folio y código QR de validación.
                </div>

                <div style="margin-top:24px; text-align:left; border-top:1px solid #e2e8f0; padding-top:16px;">
                    <div style="font-size:13px; font-weight:bold; color:#0f172a;">{{ $firmante }}</div>
                    <div style="font-size:12px; color:#64748b;">{{ $cargo }} · {{ $schoolName }}</div>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color:#f8fafc; padding:16px 28px; text-align:center; border-top:1px solid #e2e8f0; font-size:11px; color:#94a3b8;">
                Este es un mensaje institucional automático generado por la plataforma escolar {{ $schoolName }}.<br>
                Por favor, no responda directamente a este correo automático.
            </td>
        </tr>
    </table>
</body>
</html>
