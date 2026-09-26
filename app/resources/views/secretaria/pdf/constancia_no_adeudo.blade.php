<!DOCTYPE html>
<html lang="es">
@php
    $tieneAdeudo = $tieneAdeudo ?? (($pendientes ?? 0) > 0);
@endphp
<head>
    <meta charset="utf-8">
    <title>Constancia de No Adeudo - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 12mm 15mm 10mm 15mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 9.5pt;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2pt solid #0f172a;
            padding-bottom: 6pt;
            margin-bottom: 10pt;
            border-collapse: collapse;
        }
        .header-logo {
            width: 55pt;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 50pt;
            max-height: 50pt;
        }
        .header-info {
            vertical-align: middle;
            text-align: center;
            padding: 0 8pt;
        }
        .school-title {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 2pt;
        }
        .school-meta {
            font-size: 7.5pt;
            color: #475569;
            line-height: 1.25;
        }
        .header-folio {
            width: 85pt;
            vertical-align: middle;
            text-align: right;
        }
        .folio-box {
            border: 1.5pt solid #0f172a;
            border-radius: 3pt;
            padding: 3pt 5pt;
            text-align: center;
            background: #f8fafc;
        }
        .folio-label {
            font-size: 5.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .folio-number {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
        }
        .doc-title-box {
            text-align: center;
            margin: 8pt 0 10pt;
        }
        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            border-bottom: 1.5pt solid #cbd5e1;
            display: inline-block;
            padding-bottom: 2pt;
        }
        .recipient {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 8pt;
        }
        .content-paragraph {
            text-align: justify;
            margin-bottom: 8pt;
            font-size: 9.5pt;
            line-height: 1.45;
        }
        .data-card {
            background: #f8fafc;
            border: 1pt solid #cbd5e1;
            border-left: 3.5pt solid #16a34a;
            border-radius: 3pt;
            padding: 6pt 10pt;
            margin: 8pt 0;
        }
        .data-table {
            width: 100%;
            font-size: 8.5pt;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 1.5pt 0;
            vertical-align: top;
        }
        .data-table .label-col {
            width: 130pt;
            color: #64748b;
            font-weight: bold;
        }
        .data-table .val-col {
            color: #0f172a;
            font-weight: bold;
        }
        .audit-box {
            border: 1pt solid #cbd5e1;
            border-radius: 3pt;
            padding: 5pt 8pt;
            background: #ffffff;
            margin: 8pt 0;
        }
        .audit-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 3pt;
            border-bottom: 1pt solid #e2e8f0;
            padding-bottom: 2pt;
        }
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .audit-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 2pt 4pt;
            border: 0.5pt solid #cbd5e1;
            font-size: 7pt;
        }
        .audit-table td {
            padding: 2pt 4pt;
            border: 0.5pt solid #e2e8f0;
        }
        .status-badge-box {
            text-align: center;
            margin: 8pt 0;
            padding: 5pt;
            border-radius: 3pt;
            background: #ecfdf5;
            border: 1.5pt solid #16a34a;
        }
        .status-badge-text {
            font-size: 9pt;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
        }
        .signatures-table {
            width: 100%;
            margin-top: 16pt;
            border-collapse: collapse;
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
            font-size: 7pt;
            color: #64748b;
        }
        .validation-table {
            width: 100%;
            margin-top: 12pt;
            border-top: 1pt solid #cbd5e1;
            padding-top: 5pt;
            page-break-inside: avoid;
        }
        .val-qr-td {
            width: 54pt;
            vertical-align: middle;
        }
        .val-qr-td img {
            width: 50pt;
            height: 50pt;
            display: block;
        }
        .val-text-td {
            vertical-align: middle;
            font-size: 7pt;
            color: #475569;
            padding-left: 8pt;
            line-height: 1.3;
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
                    <div style="font-size:24pt;color:#16a34a;font-weight:bold;">T</div>
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
        <div class="doc-title">CONSTANCIA OFICIAL DE NO ADEUDO</div>
    </div>

    <div class="recipient">
        {{ $dirigidoA }}<br>
        <strong>P R E S E N T E .</strong>
    </div>

    <p class="content-paragraph">
        El Departamento de Administración, Control Financiero y Tesorería Escolar de <strong>{{ $setting->school_name }}</strong>, una vez realizada la **conciliación y auditoría en el sistema financiero oficial**, hace constar que el/la estudiante:
    </p>

    <div class="data-card">
        <table class="data-table">
            <tr>
                <td class="label-col">Nombre del Estudiante:</td>
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
                <td class="label-col">Grado y Grupo:</td>
                <td class="val-col">{{ optional($student->course)->name }} "{{ optional($student->course)->section }}" — {{ optional($student->course)->level }}</td>
            </tr>
            <tr>
                <td class="label-col">Ciclo Escolar Conciliado:</td>
                <td class="val-col">{{ $setting->academic_year }}</td>
            </tr>
        </table>
    </div>

    @if(!$tieneAdeudo)
        <div class="status-badge-box">
            <div class="status-badge-text">&#10003; SE ENCUENTRA 100% AL CORRIENTE DE SUS PAGOS Y OBLIGACIONES</div>
        </div>

        <p class="content-paragraph">
            Se certifica formalmente que tras revisar la cuenta del alumno en la base de datos institucional, **NO REGISTRA ADEUDO FINANCIERO ALGUNO** por conceptos de inscripción, reinscripción, colegiaturas mensuales, seguro escolar o cuotas de mantenimiento correspondientes al ciclo escolar <strong>{{ $setting->academic_year }}</strong>.
        </p>

        @if($student->payments->where('status', 'pagado')->isNotEmpty())
            <div class="audit-box">
                <div class="audit-title">Resumen de Pagos Conciliados y Validados en Sistema:</div>
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Periodo</th>
                            <th>Monto</th>
                            <th>Fecha Pago</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->payments->where('status', 'pagado')->take(3) as $p)
                            <tr>
                                <td>{{ $p->concept }}</td>
                                <td>{{ $p->period ?? 'Regular' }}</td>
                                <td>${{ number_format($p->amount, 2) }} {{ $setting->currency ?? 'MXN' }}</td>
                                <td>{{ optional($p->paid_date)->format('d/m/Y') ?? 'Registrado' }}</td>
                                <td style="color:#16a34a;font-weight:bold;">LIQUIDADO</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <div class="status-badge-box" style="background:#fef2f2;border-color:#dc2626;">
            <div class="status-badge-text" style="color:#991b1b;">&#9888; REGISTRA SALDO PENDIENTE DE ${{ number_format($pendientes, 2) }} {{ $setting->currency ?? 'MXN' }}</div>
        </div>
        <p class="content-paragraph">
            Se informa que en el sistema obran adeudos pendientes por liquidar. No se expide liberación financiera total hasta que los saldos sean regularizados en el módulo de tesorería.
        </p>
    @endif

    <p class="content-paragraph">
        Se expide la presente constancia con base en los registros fidedignos del sistema escolar a los {{ now()->translatedFormat('d \d\e F \d\e Y') }}.
    </p>

    <table class="signatures-table">
        <tr>
            <td class="sign-col">
                <div style="height: 20pt;"></div>
                <div class="sign-line">TESORERÍA Y FINANZAS</div>
                <div class="sign-sub">
                    Control de Pagos y Caja<br>
                    {{ $setting->school_name }}
                </div>
            </td>
            <td class="sign-col">
                <div style="height: 20pt;"></div>
                <div class="sign-line">{{ $setting->director ?? 'Dirección General' }}</div>
                <div class="sign-sub">
                    Dirección del Plantel<br>
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
                <strong style="color:#0f172a; font-size:7.5pt;">DOCUMENTO OFICIAL DIGITALMENTE CONCILIADO</strong><br>
                Escanee el código QR para verificar la solvencia oficial de esta constancia en tiempo real.<br>
                Folio: <strong>{{ $folio }}</strong> · Verificado en TuKardex · Emisión: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>
