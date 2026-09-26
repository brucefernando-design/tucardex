<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kárdex Académico Oficial - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 20mm 15mm 15mm 15mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            color: #1e293b;
            font-size: 10pt;
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
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            margin-bottom: 10pt;
        }
        .student-table {
            width: 100%;
            background: #f8fafc;
            border: 1pt solid #cbd5e1;
            font-size: 8.5pt;
            margin-bottom: 12pt;
            border-collapse: collapse;
        }
        .student-table td {
            padding: 4pt 8pt;
            border: 0.5pt solid #e2e8f0;
        }
        .lbl {
            color: #64748b;
            font-weight: 500;
            width: 90pt;
        }
        .val {
            font-weight: bold;
            color: #0f172a;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 12pt;
        }
        .grades-table th {
            background: #0f172a;
            color: #ffffff;
            padding: 5pt 6pt;
            text-align: center;
            font-size: 7.5pt;
            text-transform: uppercase;
            border: 1pt solid #0f172a;
        }
        .grades-table td {
            padding: 4pt 6pt;
            border: 0.5pt solid #cbd5e1;
            text-align: center;
        }
        .grades-table .subj-name {
            text-align: left;
            font-weight: bold;
            color: #1e293b;
        }
        .gpa-card {
            background: #f1f5f9;
            border: 1pt solid #94a3b8;
            padding: 6pt 10pt;
            display: table;
            width: 100%;
            margin-bottom: 16pt;
        }
        .gpa-left {
            display: table-cell;
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            vertical-align: middle;
        }
        .gpa-right {
            display: table-cell;
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
            color: #10b981;
            vertical-align: middle;
        }
        .signatures-table {
            width: 100%;
            margin-top: 30pt;
        }
        .sign-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20pt;
        }
        .sign-line {
            border-top: 1pt solid #0f172a;
            padding-top: 3pt;
            font-size: 8.5pt;
            font-weight: bold;
        }
        .sign-sub {
            font-size: 7pt;
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
                    <div style="font-size:5.5pt;color:#64748b;font-weight:bold;">FOLIO KÁRDEX</div>
                    <div style="font-size:8pt;font-weight:bold;">{{ $folio }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">HISTORIAL ACADÉMICO / KÁRDEX OFICIAL</div>

    <table class="student-table">
        <tr>
            <td class="lbl">Estudiante:</td>
            <td class="val">{{ $student->full_name }}</td>
            <td class="lbl">Matrícula:</td>
            <td class="val">{{ $student->code }}</td>
        </tr>
        <tr>
            <td class="lbl">CURP:</td>
            <td class="val">{{ $student->curp ?? $student->dni ?? 'N/D' }}</td>
            <td class="lbl">Grado / Grupo:</td>
            <td class="val">{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</td>
        </tr>
        <tr>
            <td class="lbl">Nivel:</td>
            <td class="val">{{ optional($student->course)->level ?? 'Educación Básica/Media' }}</td>
            <td class="lbl">Ciclo Escolar:</td>
            <td class="val">{{ $setting->academic_year }}</td>
        </tr>
    </table>

    <table class="grades-table">
        <thead>
            <tr>
                <th style="text-align:left;width:200pt;">Asignatura / Materia</th>
                <th style="width:45pt;">P1</th>
                <th style="width:45pt;">P2</th>
                <th style="width:45pt;">P3</th>
                <th style="width:55pt;">Promedio</th>
                <th style="width:65pt;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades as $subjectId => $subjectGrades)
                @php
                    $subj = optional($subjectGrades->first())->subject;
                    $p1 = optional($subjectGrades->where('period', '1er Trimestre')->first())->score ?? optional($subjectGrades->where('period', '1')->first())->score;
                    $p2 = optional($subjectGrades->where('period', '2do Trimestre')->first())->score ?? optional($subjectGrades->where('period', '2')->first())->score;
                    $p3 = optional($subjectGrades->where('period', '3er Trimestre')->first())->score ?? optional($subjectGrades->where('period', '3')->first())->score;
                    $avg = $subjectGrades->avg('score');
                    $isApproved = $avg >= 6.0;
                @endphp
                <tr>
                    <td class="subj-name">{{ $subj->name ?? 'Materia' }}</td>
                    <td>{{ $p1 !== null ? number_format($p1, 1) : '—' }}</td>
                    <td>{{ $p2 !== null ? number_format($p2, 1) : '—' }}</td>
                    <td>{{ $p3 !== null ? number_format($p3, 1) : '—' }}</td>
                    <td style="font-weight:bold; color:{{ $isApproved ? '#0f172a' : '#dc2626' }}">
                        {{ $avg !== null ? number_format($avg, 1) : '—' }}
                    </td>
                    <td>
                        @if($avg !== null)
                            <span style="color:{{ $isApproved ? '#16a34a' : '#dc2626' }};font-weight:bold;font-size:7.5pt;">
                                {{ $isApproved ? 'APROBADA' : 'NO ACRED.' }}
                            </span>
                        @else
                            <span style="color:#94a3b8;font-size:7.5pt;">CURSANDO</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:15pt;color:#94a3b8;">No se registran calificaciones capturadas para este estudiante.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="gpa-card">
        <div class="gpa-left">PROMEDIO GENERAL ACUMULADO DEL CICLO:</div>
        <div class="gpa-right">{{ $promedioGeneral ? number_format($promedioGeneral, 2) : 'N/D' }} / 10.00</div>
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sign-col">
                <div style="height: 35pt;"></div>
                <div class="sign-line">CONTROL ESCOLAR</div>
                <div class="sign-sub">Registro y Certificación Académica</div>
            </td>
            <td class="sign-col">
                <div style="height: 35pt;"></div>
                <div class="sign-line">{{ $setting->director ?? 'Dirección General' }}</div>
                <div class="sign-sub">Director(a) del Plantel</div>
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
                <strong style="color:#0f172a; font-size:7.5pt;">DOCUMENTO OFICIAL DE HISTORIAL ACADÉMICO</strong><br>
                Escanee el código QR para verificar la autenticidad de este Kárdex en la plataforma oficial.<br>
                Validado en TuKardex · Folio: <strong>{{ $folio }}</strong> · Fecha: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>
