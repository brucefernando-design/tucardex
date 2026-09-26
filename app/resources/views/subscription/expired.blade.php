<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Periodo de Prueba Concluido · TuKardex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-color: #16a34a;
            --brand-dark: #15803d;
            --ink: #0f172a;
            --muted: #64748b;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--ink);
        }
        .expired-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.12);
            border: 1px solid rgba(226, 232, 240, 0.8);
            max-width: 680px;
            width: 100%;
            overflow: hidden;
        }
        .header-banner {
            background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 36px 32px 28px;
            text-align: center;
            position: relative;
        }
        .badge-trial {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 30px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 12px;
        }
        .header-banner h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .school-info-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13.5px;
            color: #cbd5e1;
        }
        .content-body {
            padding: 32px;
        }
        .pricing-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .btn-whatsapp {
            background: #25d366;
            color: #ffffff;
            border: none;
            font-weight: 700;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.25);
        }
        .btn-whatsapp:hover {
            background: #20ba59;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(37, 211, 102, 0.35);
        }
        .feature-bullet {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <div class="expired-card">
        <div class="header-banner">
            <span class="badge-trial"><i class="bi bi-clock-history me-1"></i> Periodo de prueba finalizado</span>
            <h1>Tu periodo de 30 días ha concluido</h1>
            <div class="school-info-pill">
                <i class="bi bi-building"></i>
                <span>{{ $school->name }}</span>
            </div>
        </div>

        <div class="content-body">
            @if($isSchoolAdmin)
                <p class="text-secondary text-center mb-4" style="font-size: 14.5px;">
                    Esperamos que la experiencia con <strong>TuKardex</strong> haya sido de gran valor para tu institución.
                    Tus alumnos, calificaciones y configuraciones <strong>se encuentran seguros y resguardados</strong>.
                    Para reactivar el acceso completo al personal y familias, activa tu suscripción mensual.
                </p>

                <div class="pricing-box">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">Plan Seleccionado</span>
                            <h5 class="mb-0 fw-bold text-success">{{ $subscription['plan_name'] }}</h5>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small text-uppercase fw-bold">Tarifa por Alumno</span>
                            <div class="fw-bold fs-5">${{ number_format($subscription['unit_price'], 2) }} <small class="text-muted fs-6">MXN/mes</small></div>
                        </div>
                    </div>

                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-2 rounded bg-white border">
                                <small class="text-muted d-block" style="font-size:11px;">Alumnos Registrados</small>
                                <strong class="fs-6">{{ $subscription['active_students'] }} alumnos</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded bg-white border">
                                <small class="text-muted d-block" style="font-size:11px;">Inversión Mensual Estimada</small>
                                <strong class="fs-6 text-primary">${{ number_format($subscription['total'], 2) }} MXN</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-whatsapp w-100 mb-2">
                        <i class="bi bi-whatsapp fs-5"></i> Activar Suscripción por WhatsApp
                    </a>
                    <div class="text-center">
                        <small class="text-muted" style="font-size: 12px;">Atención inmediata de lunes a sábado de 8:00 a 19:00 hrs (Hora CDMX)</small>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="fw-semibold small mb-2"><i class="bi bi-info-circle text-primary me-1"></i> ¿Prefieres pago por transferencia (SPEI) o Factura CFDI?</div>
                    <p class="small text-muted mb-0">
                        Escríbenos a <a href="mailto:ventas@tukardex.com?subject={{ urlencode('Activación Colegio ' . $school->name) }}" class="fw-semibold text-decoration-none">ventas@tukardex.com</a> indicando tu razón social y te enviaremos de inmediato los datos bancarios y tu factura con validez fiscal SAT.
                    </p>
                </div>
            @else
                <div class="text-center py-4">
                    <div class="display-6 text-muted mb-3"><i class="bi bi-shield-lock"></i></div>
                    <h5>Acceso restringido temporalmente</h5>
                    <p class="text-muted small">
                        El periodo de evaluación institucional de <strong>{{ $school->name }}</strong> ha concluido.
                        Por favor comunícate con la Dirección o Administración de tu colegio para la reactivación de las cuentas.
                    </p>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="small text-muted">TuKardex · Gestión Escolar Inteligente</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
