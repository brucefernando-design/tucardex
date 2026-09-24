<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Credencial Escolar - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 8mm;
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
            font-size: 8pt;
        }
        .main-container {
            width: 100%;
            margin: 0 auto;
        }
        .instructions-panel {
            background: #f8fafc;
            border: 1pt solid #cbd5e1;
            border-left: 3.5pt solid #16a34a;
            padding: 5pt 9pt;
            margin-bottom: 12pt;
            border-radius: 4pt;
        }
        .inst-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 2pt;
        }
        .inst-text {
            font-size: 6.5pt;
            color: #475569;
            line-height: 1.35;
        }

        /* Tabla principal que contiene Anverso y Reverso */
        .cards-wrapper-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6mm 0;
            margin-bottom: 10pt;
        }
        .card-column {
            width: 50%;
            vertical-align: top;
        }
        .side-label {
            font-size: 7pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 4pt;
            letter-spacing: 0.5pt;
        }

        /* Medida estándar oficial de credencial CR-80: 85.6mm x 54mm */
        .id-card {
            width: 85.6mm;
            height: 54mm;
            border: 1.2pt solid #94a3b8;
            background: #ffffff;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            margin: 0 auto;
        }

        /* ENCABEZADO INSTITUCIONAL */
        .id-header {
            background: #0f172a;
            color: #ffffff;
            padding: 4px 6px 3px 6px;
            width: 100%;
        }
        .header-layout {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo-td {
            width: 26px;
            vertical-align: middle;
        }
        .header-logo-td img {
            width: 24px;
            height: 24px;
            border-radius: 3px;
        }
        .header-titles-td {
            vertical-align: middle;
            padding-left: 5px;
        }
        .school-title {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.1;
        }
        .school-subtitle {
            font-size: 4.8pt;
            color: #94a3b8;
            margin-top: 1px;
        }
        .accent-bar {
            height: 2.5px;
            background: #16a34a;
            width: 100%;
        }

        /* CUERPO DEL ANVERSO: DISTRIBUCIÓN PROPORCIONADA */
        .front-body {
            width: 100%;
            height: 38mm;
            border-collapse: collapse;
        }
        .photo-column {
            width: 29mm;
            vertical-align: middle;
            text-align: center;
            padding-left: 4px;
        }
        .photo-container {
            width: 26mm;
            height: 32mm;
            border: 1.5pt solid #0f172a;
            border-radius: 3px;
            background: #f1f5f9;
            overflow: hidden;
            margin: 0 auto;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
        }
        .photo-fallback {
            line-height: 32mm;
            font-size: 16pt;
            font-weight: bold;
            color: #475569;
        }
        .student-pill {
            display: inline-block;
            background: #16a34a;
            color: #ffffff;
            font-size: 4.5pt;
            font-weight: bold;
            padding: 1.5px 4px;
            border-radius: 2px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .data-column {
            vertical-align: middle;
            padding-left: 6px;
            padding-right: 6px;
        }
        
        /* Nombre del Alumno: ELEMENTO HERO (Grande y legible) */
        .student-full-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.15;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        /* Grado y Nivel destacado */
        .grade-badge-line {
            margin-bottom: 4px;
        }
        .grade-highlight {
            font-size: 7.2pt;
            font-weight: bold;
            color: #16a34a;
        }
        .level-tag {
            background: #e2e8f0;
            color: #334155;
            font-size: 4.8pt;
            font-weight: bold;
            padding: 1px 3px;
            border-radius: 2px;
            margin-left: 3px;
            text-transform: uppercase;
        }

        /* Tabla de datos estructurada y sin huecos */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6pt;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: middle;
        }
        .meta-lbl {
            color: #64748b;
            font-weight: bold;
            width: 32px;
            font-size: 5.2pt;
            text-transform: uppercase;
        }
        .meta-val {
            color: #0f172a;
            font-weight: bold;
        }
        .meta-val.code {
            font-size: 7.5pt;
            color: #0f172a;
            letter-spacing: 0.5pt;
        }

        /* PIE DEL ANVERSO */
        .id-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f1f5f9;
            border-top: 1pt solid #cbd5e1;
            padding: 2.5px 6px;
        }
        .footer-layout {
            width: 100%;
            border-collapse: collapse;
            font-size: 5pt;
        }
        .foot-cycle {
            font-weight: bold;
            color: #334155;
        }
        .foot-type {
            text-align: right;
            font-weight: bold;
            color: #16a34a;
        }

        /* ===== REVERSO ===== */
        .back-body-tbl {
            width: 100%;
            border-collapse: collapse;
            padding: 5px 6px;
        }
        .back-content-td {
            width: 54mm;
            vertical-align: top;
            padding-right: 4px;
        }
        .emergency-card {
            background: #fef2f2;
            border: 0.8pt solid #fecaca;
            border-radius: 3px;
            padding: 3px 5px;
            margin-bottom: 4px;
        }
        .emerg-header {
            font-size: 5.2pt;
            font-weight: bold;
            color: #b91c1c;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .emerg-row {
            font-size: 5pt;
            color: #1e293b;
            line-height: 1.25;
        }
        .terms-block {
            font-size: 4.6pt;
            color: #64748b;
            line-height: 1.3;
            text-align: justify;
        }
        .terms-block strong {
            color: #334155;
        }

        .back-qr-td {
            width: 25mm;
            vertical-align: top;
            text-align: center;
        }
        .qr-frame {
            width: 22mm;
            height: 22mm;
            margin: 0 auto;
        }
        .qr-frame img {
            width: 100%;
            height: 100%;
        }
        .qr-legend {
            font-size: 4pt;
            color: #64748b;
            font-weight: bold;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .sign-wrapper {
            margin-top: 6px;
            border-top: 0.8pt solid #0f172a;
            padding-top: 1px;
            text-align: center;
        }
        .sign-name {
            font-size: 4.8pt;
            font-weight: bold;
            color: #0f172a;
        }
        .sign-post {
            font-size: 3.8pt;
            color: #64748b;
        }

        /* GUÍA DE CORTE INFERIOR */
        .bottom-cut-guide {
            margin-top: 15pt;
            border-top: 1pt dashed #cbd5e1;
            padding-top: 8pt;
            font-size: 6.8pt;
            color: #64748b;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="main-container">
        
        <!-- Panel de Instrucciones Oficiales -->
        <div class="instructions-panel">
            <div class="inst-title">&#9432; Guía de Impresión y Tamaño Oficial</div>
            <div class="inst-text">
                • <strong>Proporciones Exactas:</strong> Cada cara mide exactamente <strong>8.5 cm × 5.4 cm</strong> (estándar internacional de credencial escolar).<br>
                • <strong>Ajuste de Impresora:</strong> En la ventana de impresión selecciona <strong>"Tamaño real" (100% de escala)</strong> para que no se reduzca el tamaño.<br>
                • <strong>Papel recomendado:</strong> Imprime en cartulina opalina o papel fotográfico grueso (200g - 300g), recorta por el marco exterior y enmica con plástico térmico.
            </div>
        </div>

        <table class="cards-wrapper-tbl">
            <tr>
                <!-- ANVERSO (FRENTE) -->
                <td class="card-column">
                    <div class="side-label">Cara Frontal (Anverso)</div>
                    <div class="id-card">
                        
                        <!-- Header Institucional -->
                        <div class="id-header">
                            <table class="header-layout">
                                <tr>
                                    <td class="header-logo-td">
                                        @if($setting->logo_base64)
                                            <img src="{{ $setting->logo_base64 }}">
                                        @else
                                            <span style="color:#16a34a;font-weight:bold;font-size:10pt;">T</span>
                                        @endif
                                    </td>
                                    <td class="header-titles-td">
                                        <div class="school-title">{{ \Illuminate\Support\Str::limit($setting->school_name, 34) }}</div>
                                        <div class="school-subtitle">
                                            @if($setting->cct)C.C.T. {{ $setting->cct }} &nbsp;|&nbsp; @endif
                                            @if($setting->rvoe)RVOE: {{ $setting->rvoe }} &nbsp;|&nbsp; @endif
                                            CREDENCIAL ESCOLAR
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="accent-bar"></div>

                        <!-- Contenido Principal Proporcionado -->
                        <table class="front-body">
                            <tr>
                                <!-- Foto del Alumno Grande -->
                                <td class="photo-column">
                                    <div class="photo-container">
                                        @if($student->photo_base64)
                                            <img src="{{ $student->photo_base64 }}">
                                        @else
                                            <div class="photo-fallback">{{ $student->initials }}</div>
                                        @endif
                                    </div>
                                    <div class="student-pill">ALUMNO(A)</div>
                                </td>

                                <!-- Datos del Alumno Bien Distribuidos -->
                                <td class="data-column">
                                    <div class="student-full-name">{{ $student->full_name }}</div>
                                    
                                    <div class="grade-badge-line">
                                        <span class="grade-highlight">{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</span>
                                        <span class="level-tag">{{ optional($student->course)->level ?? 'General' }}</span>
                                    </div>

                                    <table class="meta-table">
                                        <tr>
                                            <td class="meta-lbl">MAT:</td>
                                            <td class="meta-val code">{{ $student->code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="meta-lbl">CURP:</td>
                                            <td class="meta-val">{{ $student->curp ?? $student->dni ?? 'No registrada' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="meta-lbl">TURNO:</td>
                                            <td class="meta-val">{{ optional($student->course)->shift ?? 'Matutino' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Barra de Ciclo Escolar -->
                        <div class="id-footer">
                            <table class="footer-layout">
                                <tr>
                                    <td class="foot-cycle">CICLO ESCOLAR {{ $setting->academic_year }}</td>
                                    <td class="foot-type">OFICIAL VIGENTE</td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </td>

                <!-- REVERSO (VUELTA) -->
                <td class="card-column">
                    <div class="side-label">Cara Posterior (Reverso)</div>
                    <div class="id-card">
                        
                        <div class="id-header" style="background:#1e293b;">
                            <div style="font-size:5.5pt;font-weight:bold;color:#ffffff;text-align:center;text-transform:uppercase;letter-spacing:0.5pt;">
                                DATOS DE SEGURIDAD Y CONTACTO ESCOLAR
                            </div>
                        </div>
                        <div class="accent-bar"></div>

                        <table class="back-body-tbl" style="height:38mm;">
                            <tr>
                                <td class="back-content-td">
                                    <div class="emergency-card">
                                        <div class="emerg-header">&#9888; EN CASO DE EMERGENCIA</div>
                                        <div class="emerg-row"><strong>Tutor:</strong> {{ \Illuminate\Support\Str::limit($student->guardian_name ?? 'Registrado en expediente', 22) }}</div>
                                        <div class="emerg-row"><strong>Teléfono:</strong> {{ $student->guardian_phone ?? $student->phone ?? 'Sin teléfono' }}</div>
                                    </div>

                                    <div class="terms-block">
                                        • Esta credencial es personal e intransferible.<br>
                                        • Acredita al portador como alumno regular del plantel.<br>
                                        • <strong>Plantel:</strong> {{ \Illuminate\Support\Str::limit($setting->address ?? 'Instalaciones del Colegio', 30) }}<br>
                                        • <strong>Teléfono:</strong> {{ $setting->phone ?? 'Sin teléfono' }}
                                    </div>

                                    <div class="sign-wrapper">
                                        <div class="sign-name">{{ \Illuminate\Support\Str::limit($setting->director ?? 'Dirección General', 22) }}</div>
                                        <div class="sign-post">DIRECTOR(A) DEL PLANTEL</div>
                                    </div>
                                </td>

                                <td class="back-qr-td">
                                    @if($qrData)
                                        <div class="qr-frame">
                                            <img src="{{ $qrData }}">
                                        </div>
                                        <div class="qr-legend">VALIDACIÓN QR</div>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div class="id-footer">
                            <table class="footer-layout">
                                <tr>
                                    <td class="foot-cycle">SISTEMA TUCARDEX</td>
                                    <td class="foot-type" style="color:#64748b;">EXP: {{ $student->code }}</td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </td>
            </tr>
        </table>

        <div class="bottom-cut-guide">
            <strong>Instrucción de Recorte:</strong> Corta sobre las líneas exteriores de cada tarjeta. Al doblar o juntar ambas caras obtendrás una credencial de dos lados de 8.5 cm de ancho por 5.4 cm de alto.
        </div>

    </div>

</body>
</html>
