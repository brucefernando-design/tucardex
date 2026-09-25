<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Constancia de Estudios - {{ $student->full_name }}</title>
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
            line-height: 1.6;
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
            letter-spacing: 0.5pt;
            margin-bottom: 2pt;
        }
        .school-meta {
            font-size: 8pt;
            color: #475569;
            line-height: 1.3;
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
        .folio-label {
            font-size: 6pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .folio-number {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        .doc-title-box {
            text-align: center;
            margin: 20pt 0 15pt;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 2pt;
            border-bottom: 1pt solid #cbd5e1;
            display: inline-block;
            padding-bottom: 4pt;
        }
        .recipient {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 16pt;
        }
        .content-paragraph {
            text-align: justify;
            margin-bottom: 14pt;
            font-size: 10.5pt;
            line-height: 1.7;
        }
        .student-highlight {
            font-weight: bold;
            color: #0f172a;
        }
        .data-card {
            background: #f8fafc;
            border: 1pt solid #e2e8f0;
            border-left: 3pt solid #10b981;
            border-radius: 4pt;
            padding: 10pt 14pt;
            margin: 16pt 0;
        }
        .data-table {
            width: 100%;
            font-size: 9.5pt;
        }
        .data-table td {
            padding: 3pt 0;
            vertical-align: top;
        }
        .data-table .label-col {
            width: 140pt;
            color: #64748b;
            font-weight: 500;
        }
        .data-table .val-col {
            color: #0f172a;
            font-weight: bold;
        }
        .signatures-table {
            width: 100%;
            margin-top: 50pt;
        }
        .sign-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20pt;
        }
        .sign-line {
            border-top: 1pt solid #0f172a;
            padding-top: 4pt;
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
        }
        .sign-sub {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1pt;
        }
        .validation-table {
            width: 100%;
            margin-top: 25pt;
            border-top: 1pt solid #cbd5e1;
            padding-top: 8pt;
        }
        .val-qr-td {
            width: 50pt;
            vertical-align: middle;
        }
        .val-qr-td img {
            width: 46pt;
            height: 46pt;
            display: block;
        }
        .val-text-td {
            vertical-align: middle;
            font-size: 7pt;
            color: #475569;
            padding-left: 8pt;
            line-height: 1.35;
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
                    <div style="font-size:26pt;color:#10b981;">&#127891;</div>
                @endif
            </td>
            <td class="header-info">
                <div class="school-title">{{ $setting->school_name }}</div>
                <div class="school-meta">
                    @if($setting->cct)<strong>CCT:</strong> {{ $setting->cct }} &nbsp;|&nbsp; @endif
                    @if($setting->rvoe)<strong>RVOE / ACUERDO:</strong> {{ $setting->rvoe }}<br>@endif
                    {{ $setting->address }} &nbsp;·&nbsp; Tel: {{ $setting->phone }}
                </div>
            </td>
            <td class="header-folio">
                <div class="folio-box">
                    <div class="folio-label">FOLIO OFICIAL</div>
                    <div class="folio-number">{{ $folio }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title-box">
        <div class="doc-title">CONSTANCIA DE ESTUDIOS</div>
    </div>

    <div class="recipient">
        {{ $dirigidoA }}<br>
        <strong>P R E S E N T E .</strong>
    </div>

    <p class="content-paragraph">
        La Dirección y el Departamento de Control Escolar de esta institución educativa, hacen <strong>CONSTAR</strong> que según los archivos y registros oficiales que obran en este plantel, el/la estudiante:
    </p>

    <div class="data-card">
        <table class="data-table">
            <tr>
                <td class="label-col">Nombre del Estudiante:</td>
                <td class="val-col">{{ $student->full_name }}</td>
            </tr>
            <tr>
                <td class="label-col">Matrícula / Código:</td>
                <td class="val-col">{{ $student->code }}</td>
            </tr>
            <tr>
                <td class="label-col">CURP:</td>
                <td class="val-col">{{ $student->curp ?? $student->dni ?? 'No registrada' }}</td>
            </tr>
            <tr>
                <td class="label-col">Nivel y Grado:</td>
                <td class="val-col">{{ optional($student->course)->level ?? 'Educación' }} — {{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</td>
            </tr>
            <tr>
                <td class="label-col">Turno y Ciclo Escolar:</td>
                <td class="val-col">{{ optional($student->course)->shift ?? 'Matutino' }} &nbsp;|&nbsp; Ciclo Escolar {{ $setting->academic_year }}</td>
            </tr>
            @if($incluirPromedio && $promedio)
            <tr>
                <td class="label-col">Promedio General:</td>
                <td class="val-col">{{ $promedio }} / 10.00</td>
            </tr>
            @endif
        </table>
    </div>

    <p class="content-paragraph">
        Se encuentra legalmente <strong>INSCRITO(A)</strong> y cursando de manera regular las actividades académicas correspondientes al ciclo escolar <strong>{{ $setting->academic_year }}</strong>.
    </p>

    <p class="content-paragraph">
        A petición de la parte interesada y para {{ $motivo }}, se expide la presente en la ciudad sede del plantel, a los {{ now()->translatedFormat('d \d\e F \d\e Y') }}.
    </p>

    <table class="signatures-table">
        <tr>
            <td class="sign-col">
                <div style="height: 40pt;"></div>
                <div class="sign-line">{{ $setting->director ?? 'Dirección General' }}</div>
                <div class="sign-sub">
                    Director(a) del Plantel<br>
                    @if($setting->cedula_profesional)Céd. Prof. {{ $setting->cedula_profesional }}@endif
                </div>
            </td>
            <td class="sign-col">
                <div style="height: 40pt;"></div>
                <div class="sign-line">SELLO Y CONTROL ESCOLAR</div>
                <div class="sign-sub">
                    Departamento de Secretaría<br>
                    {{ $setting->school_name }}
                </div>
            </td>
        </tr>
    </table>

    <table class="validation-table">
        <tr>
            <td class="val-qr-td">
                @if(!empty($qrData))
                    <img src="{{ $qrData }}">
                @endif
            </td>
            <td class="val-text-td">
                <strong style="color:#0f172a; font-size:7.5pt;">DOCUMENTO OFICIAL DIGITALMENTE VALIDADO</strong><br>
                Validez oficial emitida por el sistema de Control Escolar de {{ $setting->school_name }}. Escanee el código QR para verificar la autenticidad en tiempo real.<br>
                Folio: <strong>{{ $folio }}</strong> &middot; Emisión: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>
