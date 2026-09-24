<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Oficio Institucional - {{ $asunto }}</title>
    <style>
        @page {
            margin: 25mm 20mm 20mm 20mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.65;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5pt solid #0f172a;
            padding-bottom: 12pt;
            margin-bottom: 20pt;
        }
        .header-logo {
            width: 70pt;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 65pt;
            max-height: 65pt;
            border-radius: 4pt;
        }
        .header-info {
            vertical-align: middle;
            text-align: center;
            padding: 0 10pt;
        }
        .school-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .school-meta {
            font-size: 8pt;
            color: #475569;
        }
        .header-folio {
            width: 90pt;
            vertical-align: middle;
            text-align: right;
        }
        .folio-box {
            border: 1.5pt solid #0f172a;
            border-radius: 4pt;
            padding: 4pt 6pt;
            text-align: center;
            background: #f8fafc;
        }
        .asunto-box {
            text-align: right;
            font-size: 9.5pt;
            margin-bottom: 20pt;
        }
        .recipient {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 18pt;
        }
        .content-paragraph {
            text-align: justify;
            margin-bottom: 14pt;
            font-size: 10.5pt;
            line-height: 1.8;
            white-space: pre-line;
        }
        .details-box {
            background: #f8fafc;
            border: 1pt solid #cbd5e1;
            border-left: 3.5pt solid #0f172a;
            border-radius: 4pt;
            padding: 10pt 14pt;
            margin: 16pt 0;
            font-size: 9.5pt;
        }
        .details-table {
            width: 100%;
        }
        .details-table td {
            padding: 3pt 0;
        }
        .details-table .lbl {
            width: 100pt;
            color: #64748b;
            font-weight: 500;
        }
        .details-table .val {
            font-weight: bold;
            color: #0f172a;
        }
        .sign-area {
            margin-top: 50pt;
            text-align: center;
        }
        .sign-line {
            border-top: 1pt solid #0f172a;
            width: 200pt;
            margin: 0 auto;
            padding-top: 4pt;
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        .sign-sub {
            font-size: 8pt;
            color: #64748b;
        }
        .val-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1pt solid #e2e8f0;
            padding-top: 6pt;
            display: table;
            width: 100%;
        }
        .val-qr {
            display: table-cell;
            width: 45pt;
            vertical-align: middle;
        }
        .val-qr img {
            width: 40pt;
            height: 40pt;
        }
        .val-txt {
            display: table-cell;
            vertical-align: middle;
            font-size: 6.5pt;
            color: #64748b;
            padding-left: 8pt;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($setting->logo_base64)
                    <img src="{{ $setting->logo_base64 }}">
                @else
                    <div style="font-size:26pt;color:#0f172a;">&#127891;</div>
                @endif
            </td>
            <td class="header-info">
                <div class="school-title">{{ $setting->school_name }}</div>
                <div class="school-meta">
                    @if($setting->cct)<strong>CCT:</strong> {{ $setting->cct }} &nbsp;|&nbsp; @endif
                    @if($setting->rvoe)<strong>RVOE:</strong> {{ $setting->rvoe }}<br>@endif
                    {{ $setting->address }} &nbsp;·&nbsp; Tel: {{ $setting->phone }}
                </div>
            </td>
            <td class="header-folio">
                <div class="folio-box">
                    <div style="font-size:6pt;color:#64748b;font-weight:bold;">FOLIO</div>
                    <div style="font-size:8.5pt;font-weight:bold;">{{ $folio }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="asunto-box">
        <strong>OFICIO:</strong> {{ strtoupper($tipo) }}<br>
        <strong>ASUNTO:</strong> {{ $asunto }}<br>
        <strong>FECHA:</strong> {{ now()->translatedFormat('d \d\e F \d\e Y') }}
    </div>

    <div class="recipient">
        {{ $destinatario }}<br>
        <strong>P R E S E N T E .</strong>
    </div>

    <div class="content-paragraph">
        {{ $cuerpo }}
    </div>

    @if($fechaCita || $horaCita)
        <div class="details-box">
            <table class="details-table">
                @if($fechaCita)
                    <tr>
                        <td class="lbl">Fecha citada:</td>
                        <td class="val">{{ \Carbon\Carbon::parse($fechaCita)->translatedFormat('l, d \d\e F \d\e Y') }}</td>
                    </tr>
                @endif
                @if($horaCita)
                    <tr>
                        <td class="lbl">Horario:</td>
                        <td class="val">{{ $horaCita }} hrs.</td>
                    </tr>
                @endif
                <tr>
                    <td class="lbl">Lugar:</td>
                    <td class="val">{{ $lugar }}</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="content-paragraph">
        Sin otro particular por el momento, agradeciendo de antemano su atención y puntual asistencia, quedo a sus apreciables órdenes.
    </div>

    <div class="sign-area">
        <div style="height: 40pt;"></div>
        <div class="sign-line">{{ $firmante }}</div>
        <div class="sign-sub">{{ $cargo }}<br>{{ $setting->school_name }}</div>
    </div>

    <div class="val-footer">
        <div class="val-qr">
            @if($qrData)
                <img src="{{ $qrData }}">
            @endif
        </div>
        <div class="val-txt">
            <strong>COMUNICACIÓN INSTITUCIONAL OFICIAL</strong><br>
            Emitido por la Secretaría de {{ $setting->school_name }} · Folio: <strong>{{ $folio }}</strong>
        </div>
    </div>

</body>
</html>
