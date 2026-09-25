<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color:#2c3e50; font-size:13px; margin:0; }
        .header { text-align:center; border-bottom:3px solid #2ecc71; padding:26px 20px 16px; }
        .header .brand { color:#27ae60; font-weight:bold; font-size:22px; }
        .header h2 { margin:6px 0 0; font-size:15px; letter-spacing:1px; }
        .header .meta { color:#7b8a99; font-size:11px; margin-top:4px; }
        .body { padding:40px 56px; line-height:1.9; text-align:justify; }
        .title { text-align:center; font-size:18px; font-weight:bold; letter-spacing:3px; margin:10px 0 36px; text-decoration:underline; }
        .body strong { color:#1f2a36; }
        .data { margin:22px 0; }
        .data td { padding:5px 0; }
        .data .lbl { color:#7b8a99; width:160px; }
        .sign { margin-top:90px; text-align:center; }
        .sign .line { border-top:1px solid #2c3e50; width:240px; margin:0 auto; padding-top:6px; }
        .footer { position:fixed; bottom:24px; left:0; right:0; text-align:center; color:#9fb0bf; font-size:10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">@if($setting->logo_base64)<img src="{{ $setting->logo_base64 }}" style="height:34px;vertical-align:middle;margin-right:8px;border-radius:6px">@else&#127891;@endif {{ $setting->school_name }}</div>
        <div class="meta">{{ $setting->address }} · Tel: {{ $setting->phone }}</div>
        <h2>CONSTANCIA DE ESTUDIOS</h2>
    </div>

    <div class="body">
        <div class="title">CONSTANCIA</div>

        <p>La Dirección del establecimiento educativo <strong>{{ $setting->school_name }}</strong>, a través de la presente, hace constar que el/la estudiante:</p>

        <table class="data">
            <tr><td class="lbl">Nombre completo:</td><td><strong>{{ $student->full_name }}</strong></td></tr>
            <tr><td class="lbl">Código / Matrícula:</td><td>{{ $student->code }}</td></tr>
            <tr><td class="lbl">CURP:</td><td><strong>{{ $student->curp ?? $student->dni ?? '—' }}</strong></td></tr>
            <tr><td class="lbl">Grado y Grupo:</td><td>{{ optional($student->course)->name }} "{{ optional($student->course)->section }}" — {{ optional($student->course)->level }}</td></tr>
            <tr><td class="lbl">Ciclo escolar:</td><td>{{ $setting->academic_year }}</td></tr>
        </table>

        <p>Se encuentra debidamente <strong>inscrito(a)</strong> y cursando regularmente sus estudios en esta institución durante el ciclo escolar <strong>{{ $setting->academic_year }}</strong>.</p>

        <p>Se expide la presente constancia a solicitud del interesado(a), para los fines que considere convenientes, en la fecha {{ now()->translatedFormat('d \d\e F \d\e Y') }}.</p>

        <div class="sign">
            <div class="line">{{ $setting->director ?? 'Dirección Académica' }}<br><span style="color:#7b8a99;font-size:11px">Director(a)</span></div>
        </div>

        @if(!empty($qrData))
        <table style="width:100%; margin-top:25px; border-top:1px solid #cbd5e1; padding-top:8px;">
            <tr>
                <td style="width:50px; vertical-align:middle;">
                    <img src="{{ $qrData }}" style="width:45px; height:45px; display:block;">
                </td>
                <td style="vertical-align:middle; padding-left:8px; font-size:8.5px; color:#64748b; line-height:1.35;">
                    <strong style="color:#1f2a36;">DOCUMENTO OFICIAL DIGITALMENTE VALIDADO</strong><br>
                    Escanee el código QR para verificar la autenticidad de esta constancia en tiempo real.<br>
                    Folio: <strong>{{ $folio ?? 'CE-'.date('Y').'-'.str_pad($student->id, 4, '0', STR_PAD_LEFT) }}</strong>
                </td>
            </tr>
        </table>
        @endif
    </div>

    <div class="footer">Documento generado por el Sistema de Gestión Escolar — {{ $setting->school_name }}</div>
</body>
</html>
