<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Planilla de Credenciales - {{ $course->full_name }}</title>
    <style>
        @page {
            margin: 6mm 8mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background: #ffffff;
            color: #0f172a;
        }
        .sheet {
            width: 100%;
        }
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3mm 3.2mm;
            margin: 0 auto;
        }
        .grid-td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        /* Medida estándar oficial CR-80: 85.6mm x 54mm */
        .card-box {
            width: 85.6mm;
            height: 54mm;
            border: 1px solid #94a3b8;
            background: #ffffff;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            margin: 0 auto;
        }

        /* ===== ANVERSO (CARA FRONTAL) ===== */
        .card-header {
            background: #0f172a;
            color: #ffffff;
            padding: 3.5px 6px 3px 6px;
            width: 100%;
        }
        .header-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 24px;
            vertical-align: middle;
        }
        .logo-cell img {
            width: 22px;
            height: 22px;
            border-radius: 3px;
        }
        .title-cell {
            vertical-align: middle;
            padding-left: 5px;
        }
        .school-name {
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.1;
        }
        .school-sub {
            font-size: 4.8pt;
            color: #94a3b8;
        }
        .green-line {
            height: 2px;
            background: #16a34a;
            width: 100%;
        }
        .card-body-tbl {
            width: 100%;
            height: 38mm;
            border-collapse: collapse;
        }
        .photo-cell {
            width: 28mm;
            vertical-align: middle;
            text-align: center;
            padding-left: 4px;
        }
        .photo-frame {
            width: 25mm;
            height: 31mm;
            border: 1.5pt solid #0f172a;
            border-radius: 3px;
            background: #f1f5f9;
            overflow: hidden;
            display: block;
            margin: 0 auto;
        }
        .photo-frame img {
            width: 100%;
            height: 100%;
        }
        .photo-frame .placeholder {
            line-height: 31mm;
            font-size: 15pt;
            font-weight: bold;
            color: #475569;
        }
        .badge {
            font-size: 4.5pt;
            font-weight: bold;
            background: #16a34a;
            color: #ffffff;
            padding: 1.5px 3.5px;
            border-radius: 2px;
            margin-top: 2px;
            display: inline-block;
            text-transform: uppercase;
        }
        .info-cell {
            vertical-align: middle;
            padding-left: 5px;
            padding-right: 5px;
        }
        .student-name {
            font-size: 7.8pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.15;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .grade-badge-line {
            margin-bottom: 3px;
        }
        .grade-highlight {
            font-size: 6.8pt;
            font-weight: bold;
            color: #16a34a;
        }
        .data-tbl {
            font-size: 5.4pt;
            width: 100%;
            border-collapse: collapse;
        }
        .data-tbl td {
            padding: 0.8px 0;
        }
        .lbl {
            color: #64748b;
            font-weight: bold;
            width: 32px;
            font-size: 5pt;
            text-transform: uppercase;
        }
        .val {
            font-weight: bold;
            color: #0f172a;
        }
        .card-foot {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8fafc;
            border-top: 1px solid #cbd5e1;
            padding: 2px 6px;
        }
        .foot-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 4.8pt;
        }
        .foot-left {
            color: #334155;
            font-weight: bold;
        }
        .foot-right {
            text-align: right;
            font-weight: bold;
            color: #16a34a;
        }

        /* ===== REVERSO (CARA POSTERIOR) ===== */
        .back-header {
            background: #1e293b;
            color: #ffffff;
            padding: 3.5px 6px;
            text-align: center;
            font-size: 5.2pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
        }
        .back-body-tbl {
            width: 100%;
            height: 38mm;
            border-collapse: collapse;
            padding: 4px 5px;
        }
        .back-content-td {
            width: 56mm;
            vertical-align: top;
            padding-right: 4px;
            padding-left: 4px;
            padding-top: 3px;
        }
        .emergency-card {
            background: #fef2f2;
            border: 0.7pt solid #fecaca;
            border-radius: 3px;
            padding: 2.5px 4.5px;
            margin-bottom: 3px;
        }
        .emerg-header {
            font-size: 4.8pt;
            font-weight: bold;
            color: #b91c1c;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .emerg-row {
            font-size: 4.8pt;
            color: #1e293b;
            line-height: 1.25;
        }
        .terms-block {
            font-size: 4.4pt;
            color: #64748b;
            line-height: 1.25;
            text-align: justify;
            margin-bottom: 3px;
        }
        .sign-wrapper {
            margin-top: 4px;
            border-top: 0.7pt solid #0f172a;
            padding-top: 1px;
            text-align: center;
        }
        .sign-name {
            font-size: 4.6pt;
            font-weight: bold;
            color: #0f172a;
        }
        .sign-post {
            font-size: 3.6pt;
            color: #64748b;
        }
        .back-qr-td {
            width: 24mm;
            vertical-align: top;
            text-align: center;
            padding-top: 4px;
            padding-right: 4px;
        }
        .qr-frame {
            width: 21mm;
            height: 21mm;
            margin: 0 auto;
        }
        .qr-frame img {
            width: 100%;
            height: 100%;
        }
        .qr-legend {
            font-size: 3.8pt;
            color: #64748b;
            font-weight: bold;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .page-break {
            page-break-after: always;
        }
        .sheet-label {
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-bottom: 1.5mm;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

@php
    $modo = $tipo ?? 'frente_reverso';
@endphp

@if($modo === 'duplex')
    {{-- MODO DÚPLEX: 8 ALUMNOS POR HOJA (PÁG 1: FRENTES, PÁG 2: REVERSOS EN ESPEJO) --}}
    @php
        $duplexChunks = $students->chunk(8);
    @endphp

    @foreach($duplexChunks as $pageIndex => $pageStudents)
        {{-- PÁGINA IMPAR: CARA FRONTAL (ANVERSOS) --}}
        <div class="sheet">
            <div class="sheet-label">Cara Frontal (Anversos) · Hoja {{ ($pageIndex * 2) + 1 }} · {{ $course->full_name }}</div>
            <table class="grid-table">
                @foreach($pageStudents->chunk(2) as $row)
                    <tr>
                        @foreach($row as $st)
                            <td class="grid-td">
                                <div class="card-box">
                                    {{-- HEADER ANVERSO --}}
                                    <div class="card-header">
                                        <table class="header-tbl">
                                            <tr>
                                                <td class="logo-cell">
                                                    @if($setting->logo_base64)
                                                        <img src="{{ $setting->logo_base64 }}">
                                                    @else
                                                        <span style="color:#16a34a;font-weight:bold;font-size:8pt">T</span>
                                                    @endif
                                                </td>
                                                <td class="title-cell">
                                                    <div class="school-name">{{ \Illuminate\Support\Str::limit($setting->school_name, 32) }}</div>
                                                    <div class="school-sub">CCT: {{ $setting->cct ?? 'OFICIAL' }} · CICLO {{ $setting->academic_year }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="green-line"></div>

                                    {{-- BODY ANVERSO --}}
                                    <table class="card-body-tbl">
                                        <tr>
                                            <td class="photo-cell">
                                                <div class="photo-frame">
                                                    @if($st->photo_base64)
                                                        <img src="{{ $st->photo_base64 }}">
                                                    @else
                                                        <div class="placeholder">{{ $st->initials }}</div>
                                                    @endif
                                                </div>
                                                <div class="badge">ESTUDIANTE</div>
                                            </td>
                                            <td class="info-cell">
                                                <div class="student-name">{{ $st->full_name }}</div>
                                                <div class="grade-badge-line">
                                                    <span class="grade-highlight">{{ optional($st->course)->name }} "{{ optional($st->course)->section }}"</span>
                                                </div>
                                                <table class="data-tbl">
                                                    <tr>
                                                        <td class="lbl">MAT:</td>
                                                        <td class="val">{{ $st->code }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="lbl">CURP:</td>
                                                        <td class="val">{{ $st->curp ?? $st->dni ?? 'N/D' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="lbl">TURNO:</td>
                                                        <td class="val">{{ optional($st->course)->shift ?? 'Matutino' }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- FOOTER ANVERSO --}}
                                    <div class="card-foot">
                                        <table class="foot-tbl">
                                            <tr>
                                                <td class="foot-left">CICLO: {{ $setting->academic_year }}</td>
                                                <td class="foot-right">{{ $course->full_name }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                        @if($row->count() < 2)
                            <td class="grid-td"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="page-break"></div>

        {{-- PÁGINA PAR: CARA POSTERIOR (REVERSOS EN ESPEJO PARA DÚPLEX) --}}
        <div class="sheet">
            <div class="sheet-label">Cara Posterior (Reversos en Espejo para Impresión Dúplex) · Hoja {{ ($pageIndex * 2) + 2 }}</div>
            <table class="grid-table">
                @foreach($pageStudents->chunk(2) as $row)
                    @php
                        // En espejo horizontal: la columna 1 se intercambia con la columna 2 para coincidir al imprimir vuelta y vuelta
                        $reversedRow = $row->count() === 2 ? collect([$row[1], $row[0]]) : collect([null, $row[0]]);
                    @endphp
                    <tr>
                        @foreach($reversedRow as $st)
                            <td class="grid-td">
                                @if($st)
                                    <div class="card-box">
                                        {{-- HEADER REVERSO --}}
                                        <div class="back-header">DATOS DE SEGURIDAD Y CONTACTO ESCOLAR</div>
                                        <div class="green-line"></div>

                                        {{-- BODY REVERSO --}}
                                        <table class="back-body-tbl">
                                            <tr>
                                                <td class="back-content-td">
                                                    <div class="emergency-card">
                                                        <div class="emerg-header">&#9888; EN CASO DE EMERGENCIA</div>
                                                        <div class="emerg-row"><strong>Tutor:</strong> {{ \Illuminate\Support\Str::limit($st->guardian_name ?? 'Registrado en expediente', 24) }}</div>
                                                        <div class="emerg-row"><strong>Tel:</strong> {{ $st->guardian_phone ?? $st->phone ?? 'Sin teléfono' }}</div>
                                                    </div>

                                                    <div class="terms-block">
                                                        • Credencial personal e intransferible.<br>
                                                        • Acredita al portador como alumno regular.<br>
                                                        • <strong>Plantel:</strong> {{ \Illuminate\Support\Str::limit($setting->address ?? 'Instalaciones del Colegio', 30) }}<br>
                                                        • <strong>Tel:</strong> {{ $setting->phone ?? 'Sin teléfono' }}
                                                    </div>

                                                    <div class="sign-wrapper">
                                                        <div class="sign-name">{{ \Illuminate\Support\Str::limit($setting->director ?? 'Dirección General', 22) }}</div>
                                                        <div class="sign-post">DIRECTOR(A) DEL PLANTEL</div>
                                                    </div>
                                                </td>

                                                <td class="back-qr-td">
                                                    @if(!empty($st->qrData))
                                                        <div class="qr-frame">
                                                            <img src="{{ $st->qrData }}">
                                                        </div>
                                                        <div class="qr-legend">VALIDACIÓN QR</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- FOOTER REVERSO --}}
                                        <div class="card-foot">
                                            <table class="foot-tbl">
                                                <tr>
                                                    <td class="foot-left">SISTEMA TUCARDEX</td>
                                                    <td class="foot-right" style="color:#64748b;">EXP: {{ $st->code }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

@else
    {{-- MODO FRENTE Y REVERSO CONTIGUOS (LADO A LADO) PARA ENMICAR / DOBLAR --}}
    @php
        $sideChunks = $students->chunk(4); // 4 alumnos por hoja (4 frentes + 4 reversos = 8 tarjetas)
    @endphp

    @foreach($sideChunks as $pageIndex => $pageStudents)
        <div class="sheet">
            <div class="sheet-label">Planilla de Credenciales Oficiales (Frente y Reverso Contiguos) · Pág. {{ $pageIndex + 1 }} · {{ $course->full_name }}</div>
            <table class="grid-table">
                @foreach($pageStudents as $st)
                    <tr>
                        {{-- COLUMNA 1: ANVERSO (FRENTE) --}}
                        <td class="grid-td">
                            <div class="card-box">
                                <div class="card-header">
                                    <table class="header-tbl">
                                        <tr>
                                            <td class="logo-cell">
                                                @if($setting->logo_base64)
                                                    <img src="{{ $setting->logo_base64 }}">
                                                @else
                                                    <span style="color:#16a34a;font-weight:bold;font-size:8pt">T</span>
                                                @endif
                                            </td>
                                            <td class="title-cell">
                                                <div class="school-name">{{ \Illuminate\Support\Str::limit($setting->school_name, 32) }}</div>
                                                <div class="school-sub">CCT: {{ $setting->cct ?? 'OFICIAL' }} · CICLO {{ $setting->academic_year }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="green-line"></div>

                                <table class="card-body-tbl">
                                    <tr>
                                        <td class="photo-cell">
                                            <div class="photo-frame">
                                                @if($st->photo_base64)
                                                    <img src="{{ $st->photo_base64 }}">
                                                @else
                                                    <div class="placeholder">{{ $st->initials }}</div>
                                                @endif
                                            </div>
                                            <div class="badge">ESTUDIANTE</div>
                                        </td>
                                        <td class="info-cell">
                                            <div class="student-name">{{ $st->full_name }}</div>
                                            <div class="grade-badge-line">
                                                <span class="grade-highlight">{{ optional($st->course)->name }} "{{ optional($st->course)->section }}"</span>
                                            </div>
                                            <table class="data-tbl">
                                                <tr>
                                                    <td class="lbl">MAT:</td>
                                                    <td class="val">{{ $st->code }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="lbl">CURP:</td>
                                                    <td class="val">{{ $st->curp ?? $st->dni ?? 'N/D' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="lbl">TURNO:</td>
                                                    <td class="val">{{ optional($st->course)->shift ?? 'Matutino' }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <div class="card-foot">
                                    <table class="foot-tbl">
                                        <tr>
                                            <td class="foot-left">CICLO: {{ $setting->academic_year }}</td>
                                            <td class="foot-right">{{ $course->full_name }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>

                        {{-- COLUMNA 2: REVERSO (CARA POSTERIOR) DEL MISMO ALUMNO --}}
                        <td class="grid-td">
                            <div class="card-box">
                                <div class="back-header">DATOS DE SEGURIDAD Y CONTACTO ESCOLAR</div>
                                <div class="green-line"></div>

                                <table class="back-body-tbl">
                                    <tr>
                                        <td class="back-content-td">
                                            <div class="emergency-card">
                                                <div class="emerg-header">&#9888; EN CASO DE EMERGENCIA</div>
                                                <div class="emerg-row"><strong>Tutor:</strong> {{ \Illuminate\Support\Str::limit($st->guardian_name ?? 'Registrado en expediente', 24) }}</div>
                                                <div class="emerg-row"><strong>Tel:</strong> {{ $st->guardian_phone ?? $st->phone ?? 'Sin teléfono' }}</div>
                                            </div>

                                            <div class="terms-block">
                                                • Credencial personal e intransferible.<br>
                                                • Acredita al portador como alumno regular.<br>
                                                • <strong>Plantel:</strong> {{ \Illuminate\Support\Str::limit($setting->address ?? 'Instalaciones del Colegio', 30) }}<br>
                                                • <strong>Tel:</strong> {{ $setting->phone ?? 'Sin teléfono' }}
                                            </div>

                                            <div class="sign-wrapper">
                                                <div class="sign-name">{{ \Illuminate\Support\Str::limit($setting->director ?? 'Dirección General', 22) }}</div>
                                                <div class="sign-post">DIRECTOR(A) DEL PLANTEL</div>
                                            </div>
                                        </td>

                                        <td class="back-qr-td">
                                            @if(!empty($st->qrData))
                                                <div class="qr-frame">
                                                    <img src="{{ $st->qrData }}">
                                                </div>
                                                <div class="qr-legend">VALIDACIÓN QR</div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                <div class="card-foot">
                                    <table class="foot-tbl">
                                        <tr>
                                            <td class="foot-left">SISTEMA TUCARDEX</td>
                                            <td class="foot-right" style="color:#64748b;">EXP: {{ $st->code }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

@endif

</body>
</html>
