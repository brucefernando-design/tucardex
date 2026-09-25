<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Carta de Buena Conducta - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 13mm 16mm 11mm 16mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 10.5pt;
            line-height: 1.55;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2pt solid #0f172a;
            padding-bottom: 8pt;
            margin-bottom: 11pt;
        }
        .header-logo {
            width: 60pt;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 55pt;
            max-height: 55pt;
            border-radius: 4pt;
        }
        .header-info {
            vertical-align: middle;
            text-align: center;
            padding: 0 8pt;
        }
        .school-title {
            font-size: 13.5pt;
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
            margin: 10pt 0 11pt;
        }
        .doc-title {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 2pt;
            border-bottom: 1pt solid #cbd5e1;
            display: inline-block;
            padding-bottom: 3pt;
        }
        .recipient {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 9pt;
        }
        .content-paragraph {
            text-align: justify;
            margin-bottom: 9pt;
            font-size: 10pt;
            line-height: 1.55;
        }
        .data-card {
            background: #f8fafc;
            border: 1pt solid #e2e8f0;
            border-left: 3pt solid #3b82f6;
            border-radius: 4pt;
            padding: 7pt 12pt;
            margin: 9pt 0;
        }
        .data-table {
            width: 100%;
            font-size: 9pt;
        }
        .data-table td {
            padding: 2pt 0;
            vertical-align: top;
        }
        .data-table .label-col {
            width: 135pt;
            color: #64748b;
            font-weight: 500;
        }
        .data-table .val-col {
            color: #0f172a;
            font-weight: bold;
        }
        .signatures-table {
            width: 100%;
            margin-top: 22pt;
            page-break-inside: avoid;
        }
        .sign-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 15pt;
        }
        .sign-line {
            border-top: 1pt solid #0f172a;
            padding-top: 3pt;
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        .sign-sub {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1pt;
            line-height: 1.25;
        }
        .validation-table {
            width: 100%;
            margin-top: 13pt;
            border-top: 1pt solid #cbd5e1;
            padding-top: 5pt;
            page-break-inside: avoid;
        }
        .val-qr-td {
            width: 58pt;
            vertical-align: middle;
        }
        .val-qr-td img {
            width: 54pt;
            height: 54pt;
            display: block;
        }
        .val-text-td {
            vertical-align: middle;
            font-size: 7.5pt;
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
                    <div style="font-size:26pt;color:#3b82f6;">&#127891;</div>
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
                    <div class="folio-label">FOLIO OFICIAL</div>
                    <div class="folio-number">{{ $folio }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title-box">
        <div class="doc-title">CARTA DE BUENA CONDUCTA</div>
    </div>

    <div class="recipient">
        {{ $dirigidoA }}<br>
        <strong>P R E S E N T E .</strong>
    </div>

    <p class="content-paragraph">
        Por medio de la presente, la Dirección y el Departamento de Orientación y Disciplina Escolar de <strong>{{ $setting->school_name }}</strong>, extienden su reconocimiento y hacen constar que el/la alumno(a):
    </p>

    <div class="data-card">
        <table class="data-table">
            <tr>
                <td class="label-col">Nombre del Alumno(a):</td>
                <td class="val-col">{{ $student->full_name }}</td>
            </tr>
            <tr>
                <td class="label-col">Matrícula Escolar:</td>
                <td class="val-col">{{ $student->code }}</td>
            </tr>
            <tr>
                <td class="label-col">CURP:</td>
                <td class="val-col">{{ $student->curp ?? $student->dni ?? 'No registrada' }}</td>
            </tr>
            <tr>
                <td class="label-col">Grado y Grupo Actual:</td>
                <td class="val-col">{{ optional($student->course)->name }} "{{ optional($student->course)->section }}" — {{ optional($student->course)->level }}</td>
            </tr>
            <tr>
                <td class="label-col">Ciclo Escolar:</td>
                <td class="val-col">{{ $setting->academic_year }}</td>
            </tr>
        </table>
    </div>

    <p class="content-paragraph">
        {{ $observaciones }}
    </p>

    <p class="content-paragraph">
        Asimismo, se hace constar que en su expediente disciplinario <strong>no obran reportes graves ni faltas al reglamento escolar</strong>, habiéndose distinguido por su respeto hacia las autoridades educativas, docentes, personal administrativo y compañeros de clase.
    </p>

    <p class="content-paragraph">
        Se extiende la presente constancia para los fines que al interesado convengan, en la sede de este instituto, el día {{ now()->translatedFormat('d \d\e F \d\e Y') }}.
    </p>

    <table class="signatures-table">
        <tr>
            <td class="sign-col">
                <div style="height: 26pt;"></div>
                <div class="sign-line">{{ $setting->director ?? 'Dirección General' }}</div>
                <div class="sign-sub">
                    Director(a) del Plantel<br>
                    {{ $setting->school_name }}
                </div>
            </td>
            <td class="sign-col">
                <div style="height: 26pt;"></div>
                <div class="sign-line">SECRETARÍA Y CONTROL ESCOLAR</div>
                <div class="sign-sub">
                    Validación y Registro<br>
                    Sello Oficial
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
                <strong style="color:#0f172a; font-size:7.5pt;">DOCUMENTO OFICIAL EMITIDO POR CONTROL ESCOLAR</strong><br>
                Escanee el código QR para verificar la autenticidad de esta carta en tiempo real.<br>
                Folio: <strong>{{ $folio }}</strong> · {{ $setting->school_name }} · Emisión: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>
