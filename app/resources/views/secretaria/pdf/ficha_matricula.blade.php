<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de Inscripción - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 18mm 15mm 15mm 15mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 9.5pt;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2pt solid #0f172a;
            padding-bottom: 8pt;
            margin-bottom: 12pt;
        }
        .header-logo {
            width: 55pt;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 50pt;
            max-height: 50pt;
            border-radius: 4pt;
        }
        .header-info {
            vertical-align: middle;
            text-align: center;
        }
        .school-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .school-meta {
            font-size: 7.5pt;
            color: #475569;
        }
        .header-folio {
            width: 80pt;
            vertical-align: middle;
            text-align: right;
        }
        .folio-box {
            border: 1pt solid #0f172a;
            padding: 3pt 5pt;
            text-align: center;
            background: #f8fafc;
        }
        .doc-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            margin-bottom: 10pt;
            background: #0f172a;
            color: #ffffff;
            padding: 4pt;
            border-radius: 2pt;
        }
        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            border-bottom: 1.5pt solid #10b981;
            padding-bottom: 2pt;
            margin: 10pt 0 6pt;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 6pt;
        }
        .data-table td {
            padding: 3pt 6pt;
            border: 0.5pt solid #cbd5e1;
        }
        .lbl {
            background: #f8fafc;
            color: #64748b;
            font-weight: 500;
            width: 110pt;
            font-size: 8pt;
        }
        .val {
            font-weight: bold;
            color: #0f172a;
        }
        .commitment-text {
            font-size: 7.5pt;
            color: #475569;
            text-align: justify;
            line-height: 1.4;
            border: 1pt solid #e2e8f0;
            padding: 6pt 8pt;
            border-radius: 3pt;
            margin-top: 10pt;
        }
        .signatures-table {
            width: 100%;
            margin-top: 35pt;
        }
        .sign-col {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10pt;
        }
        .sign-line {
            border-top: 1pt solid #0f172a;
            padding-top: 3pt;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .sign-sub {
            font-size: 6.5pt;
            color: #64748b;
        }
        .val-footer-table {
            width: 100%;
            margin-top: 20pt;
            border-top: 1pt solid #cbd5e1;
            padding-top: 6pt;
        }
        .val-qr-td {
            width: 45pt;
            vertical-align: middle;
        }
        .val-qr-td img {
            width: 40pt;
            height: 40pt;
            display: block;
        }
        .val-txt-td {
            vertical-align: middle;
            font-size: 6.5pt;
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
                    <div style="font-size:24pt;color:#10b981;">&#127891;</div>
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
                    <div style="font-size:5.5pt;color:#64748b;font-weight:bold;">FOLIO EXPEDIENTE</div>
                    <div style="font-size:8pt;font-weight:bold;">{{ $folio }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">CÉDULA Y FICHA OFICIAL DE INSCRIPCIÓN · CICLO {{ $setting->academic_year }}</div>

    <div class="section-title">1. DATOS GENERALES DEL ESTUDIANTE</div>
    <table class="data-table">
        <tr>
            <td class="lbl">Nombre Completo:</td>
            <td class="val" colspan="3">{{ $student->full_name }}</td>
        </tr>
        <tr>
            <td class="lbl">Matrícula / Código:</td>
            <td class="val">{{ $student->code }}</td>
            <td class="lbl">CURP:</td>
            <td class="val">{{ $student->curp ?? $student->dni ?? 'No registrada' }}</td>
        </tr>
        <tr>
            <td class="lbl">Fecha de Nacimiento:</td>
            <td class="val">{{ optional($student->birth_date)->format('d/m/Y') ?? 'N/D' }}</td>
            <td class="lbl">Género:</td>
            <td class="val">{{ $student->gender === 'M' ? 'Masculino' : ($student->gender === 'F' ? 'Femenino' : 'N/D') }}</td>
        </tr>
        <tr>
            <td class="lbl">Dirección:</td>
            <td class="val" colspan="3">{{ $student->address ?? 'Registrada en expediente físico' }}</td>
        </tr>
        <tr>
            <td class="lbl">Teléfono / Celular:</td>
            <td class="val">{{ $student->phone ?? 'N/D' }}</td>
            <td class="lbl">Correo Electrónico:</td>
            <td class="val">{{ $student->email ?? 'N/D' }}</td>
        </tr>
    </table>

    <div class="section-title">2. ADSCRIPCIÓN ACADÉMICA</div>
    <table class="data-table">
        <tr>
            <td class="lbl">Nivel Educativo:</td>
            <td class="val">{{ optional($student->course)->level ?? 'General' }}</td>
            <td class="lbl">Grado y Grupo:</td>
            <td class="val">{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</td>
        </tr>
        <tr>
            <td class="lbl">Turno:</td>
            <td class="val">{{ optional($student->course)->shift ?? 'Matutino' }}</td>
            <td class="lbl">Fecha de Ingreso:</td>
            <td class="val">{{ optional($student->enrollment_date)->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Tutor / Asesor de Grupo:</td>
            <td class="val" colspan="3">{{ optional(optional($student->course)->tutor)->full_name ?? 'Por asignar' }}</td>
        </tr>
    </table>

    <div class="section-title">3. DATOS DEL PADRE, MADRE O TUTOR LEGAL</div>
    <table class="data-table">
        <tr>
            <td class="lbl">Nombre del Tutor:</td>
            <td class="val" colspan="3">{{ $student->guardian_name ?? 'No registrado' }}</td>
        </tr>
        <tr>
            <td class="lbl">Teléfono de Contacto:</td>
            <td class="val">{{ $student->guardian_phone ?? 'No registrado' }}</td>
            <td class="lbl">Parentesco / Relación:</td>
            <td class="val">Tutor Legal</td>
        </tr>
    </table>

    <div class="commitment-text">
        <strong>COMPROMISO Y CONFORMIDAD:</strong> Por medio de la firma de este documento, el padre, madre o tutor legal y el estudiante declaran que los datos proporcionados son verídicos, y se comprometen a respetar y acatar el Reglamento General Interno de la institución, así como cumplir puntualmente con los deberes académicos y administrativos correspondientes.
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sign-col">
                <div style="height: 30pt;"></div>
                <div class="sign-line">FIRMA DEL TUTOR</div>
                <div class="sign-sub">{{ $student->guardian_name ?? 'Padre o Tutor' }}</div>
            </td>
            <td class="sign-col">
                <div style="height: 30pt;"></div>
                <div class="sign-line">FIRMA DEL ALUMNO</div>
                <div class="sign-sub">{{ $student->full_name }}</div>
            </td>
            <td class="sign-col">
                <div style="height: 30pt;"></div>
                <div class="sign-line">SECRETARÍA Y CONTROL ESCOLAR</div>
                <div class="sign-sub">Sello de Recibido</div>
            </td>
        </tr>
    </table>

    <table class="val-footer-table">
        <tr>
            <td class="val-qr-td">
                @if(!empty($qrData))
                    <img src="{{ $qrData }}">
                @endif
            </td>
            <td class="val-txt-td">
                <strong style="color:#0f172a; font-size:7.5pt;">EXPEDIENTE OFICIAL DE MATRÍCULA ESCOLAR</strong><br>
                Escanee el código QR para verificar la autenticidad de esta ficha de inscripción.<br>
                Registrado en TuCardex · Folio: <strong>{{ $folio }}</strong> · Fecha de emisión: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>
