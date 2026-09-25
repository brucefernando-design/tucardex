<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $found ? 'Documento Verificado · ' . $verification->doc_title : 'Verificación de Documento' }} · TuCardex</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            background-color: #F4F7F5;
            color: #0F172A;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background-color: #0B1A14;
            color: #ffffff;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #16A34A;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-decoration: none;
        }
        .brand-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #16A34A;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
        }
        .topbar-right {
            font-size: 12px;
            color: #94A3B8;
            font-weight: 500;
        }
        .container {
            max-width: 620px;
            width: 100%;
            margin: 32px auto;
            padding: 0 16px;
            flex: 1;
        }
        .card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #E2E8F0;
        }
        .seal-banner {
            padding: 28px 24px;
            text-align: center;
            background: linear-gradient(135deg, #0B1A14 0%, #112A20 100%);
            color: #ffffff;
            position: relative;
        }
        .seal-banner.invalid {
            background: linear-gradient(135deg, #450A0A 0%, #7F1D1D 100%);
        }
        .seal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(22, 163, 74, 0.2);
            border: 2px solid #22C55E;
            color: #4ADE80;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 12px;
        }
        .seal-banner.invalid .seal-icon {
            background: rgba(239, 68, 68, 0.2);
            border-color: #F87171;
            color: #FCA5A5;
        }
        .seal-badge {
            display: inline-block;
            background: #16A34A;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 5px 14px;
            border-radius: 9999px;
            margin-bottom: 8px;
        }
        .seal-banner.invalid .seal-badge {
            background: #DC2626;
        }
        .seal-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .seal-sub {
            font-size: 13px;
            color: #94A3B8;
        }
        .card-body {
            padding: 24px;
        }
        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 20px;
        }
        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-item.full {
            grid-column: 1 / -1;
        }
        .info-label {
            font-size: 11px;
            color: #64748B;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #DCFCE7;
            color: #15803D;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 9999px;
            width: fit-content;
        }
        .meta-box {
            border-top: 1px dashed #CBD5E1;
            padding-top: 16px;
            margin-top: 8px;
            font-size: 12px;
            color: #64748B;
            line-height: 1.5;
        }
        .token-code {
            font-family: monospace;
            background: #F1F5F9;
            padding: 2px 6px;
            border-radius: 4px;
            color: #334155;
            font-size: 11px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #64748B;
        }
    </style>
</head>
<body>

    <header class="topbar">
        <a href="{{ url('/') }}" class="brand">
            <span class="brand-badge"><i class="bi bi-mortarboard-fill"></i></span>
            <span>TuCardex</span>
        </a>
        <div class="topbar-right">
            <i class="bi bi-shield-lock-fill text-success"></i> Validador Oficial SEP / Escolar
        </div>
    </header>

    <main class="container">
        @if($found && $verification)
            <div class="card">
                <div class="seal-banner">
                    <div class="seal-icon">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <div>
                        <span class="seal-badge"><i class="bi bi-check-circle-fill"></i> Documento Oficial Auténtico</span>
                    </div>
                    <h1 class="seal-title">{{ $verification->doc_title }}</h1>
                    <p class="seal-sub">{{ $verification->school_name }} @if($verification->school_cct) · CCT: {{ $verification->school_cct }} @endif</p>
                </div>

                <div class="card-body">
                    <div class="section-label">
                        <i class="bi bi-person-vcard text-success"></i> Datos de Acreditación del Estudiante
                    </div>

                    <div class="info-grid">
                        <div class="info-item full">
                            <span class="info-label">Nombre Completo del Alumno(a)</span>
                            <span class="info-value" style="font-size: 16px;">{{ $verification->student_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Matrícula Escolar</span>
                            <span class="info-value">{{ $verification->student_code ?: 'N/D' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">CURP / Identificación</span>
                            <span class="info-value">{{ $verification->student_curp ?: 'Registrada en plantel' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Grado y Grupo</span>
                            <span class="info-value">{{ $verification->course_name ?: 'General' }} @if($verification->level) ({{ $verification->level }}) @endif</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ciclo Escolar</span>
                            <span class="info-value">{{ $verification->academic_year ?: date('Y') }}</span>
                        </div>
                        <div class="info-item full">
                            <span class="info-label">Situación Escolar Actual</span>
                            <div class="mt-1">
                                <span class="status-pill">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Alumno(a) Regular Activo e Inscrito
                                </span>
                            </div>
                        </div>
                    </div>

                    @if(!empty($verification->extra_data))
                        <div class="section-label">
                            <i class="bi bi-award text-success"></i> Certificación del Documento
                        </div>
                        <div class="info-grid">
                            @if(isset($verification->extra_data['promedio']))
                                <div class="info-item">
                                    <span class="info-label">Promedio General Oficial</span>
                                    <span class="info-value" style="color: #16A34A; font-size: 16px;">{{ $verification->extra_data['promedio'] }}</span>
                                </div>
                            @endif
                            @if(isset($verification->extra_data['asistencia']))
                                <div class="info-item">
                                    <span class="info-label">Asistencia Registrada</span>
                                    <span class="info-value">{{ $verification->extra_data['asistencia'] }}</span>
                                </div>
                            @endif
                            @if(isset($verification->extra_data['estatus_financiero']))
                                <div class="info-item full">
                                    <span class="info-label">Estatus Administrativo</span>
                                    <span class="info-value" style="color: #16A34A;">{{ $verification->extra_data['estatus_financiero'] }}</span>
                                </div>
                            @endif
                            @if(isset($verification->extra_data['conducta']))
                                <div class="info-item full">
                                    <span class="info-label">Dictamen de Conducta</span>
                                    <span class="info-value">{{ $verification->extra_data['conducta'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="section-label">
                        <i class="bi bi-building-check text-success"></i> Datos de Emisión Institucional
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Plantel Emisor</span>
                            <span class="info-value">{{ $verification->school_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Folio Oficial</span>
                            <span class="info-value">{{ $verification->folio ?: strtoupper(substr($verification->token, 0, 10)) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Autoridad Firmante</span>
                            <span class="info-value">{{ $verification->director_name ?: 'Dirección General del Plantel' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha de Emisión</span>
                            <span class="info-value">{{ optional($verification->issued_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="meta-box">
                        <div style="margin-bottom: 4px;">
                            <i class="bi bi-fingerprint text-success"></i> <strong>Sello Digital TuCardex:</strong>
                            <span class="token-code">{{ $verification->token }}</span>
                        </div>
                        <div>
                            La información aquí mostrada proviene directamente de la base de datos oficial del plantel en <strong>TuCardex</strong>. Cualquier alteración física en el documento impreso que difiera de estos registros carece de validez oficial.
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="seal-banner invalid">
                    <div class="seal-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <span class="seal-badge">No Verificado</span>
                    </div>
                    <h1 class="seal-title">Código de Verificación No Reconocido</h1>
                    <p class="seal-sub">No se encontró un registro oficial asociado a este código QR</p>
                </div>
                <div class="card-body" style="text-align: center; padding: 32px 24px;">
                    <p style="color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 16px;">
                        El código QR escaneado no coincide con ningún documento oficial activo emitido a través del sistema de Control Escolar <strong>TuCardex</strong>, o bien el folio ha sido dado de baja.
                    </p>
                    <a href="{{ url('/') }}" style="display: inline-block; background: #0B1A14; color: #fff; text-decoration: none; padding: 10px 22px; border-radius: 9999px; font-size: 13px; font-weight: 600;">
                        Ir al Portal Principal
                    </a>
                </div>
            </div>
        @endif
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} <strong>TuCardex</strong> · Plataforma de Gestión y Control Escolar Oficial · Verificado el {{ now()->format('d/m/Y H:i') }}
    </footer>

</body>
</html>
