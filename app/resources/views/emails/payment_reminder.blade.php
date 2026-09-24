<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Pago</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrapper { width: 100%; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
        .header { background: linear-gradient(135deg, #065f46, #047857); color: #ffffff; padding: 32px 28px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 6px 0 0 0; opacity: 0.9; font-size: 14px; }
        .body { padding: 32px 28px; }
        .greeting { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 12px; }
        .intro { font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px; }
        .card-details { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 28px; }
        .row-detail { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #cbd5e1; font-size: 14px; }
        .row-detail:last-child { border-bottom: none; }
        .label { color: #64748b; font-weight: 500; }
        .value { color: #0f172a; font-weight: 700; text-align: right; }
        .total-row { padding-top: 12px; font-size: 18px; color: #047857; font-weight: 800; }
        .btn-pay { display: block; width: 100%; box-sizing: border-box; background: #047857; color: #ffffff !important; text-align: center; padding: 16px 20px; font-size: 16px; font-weight: 700; text-decoration: none; border-radius: 8px; margin: 24px 0 16px 0; box-shadow: 0 4px 10px rgba(4,120,87,0.3); }
        .methods { text-align: center; font-size: 12px; color: #64748b; margin-bottom: 24px; }
        .methods span { display: inline-block; background: #f1f5f9; padding: 4px 10px; border-radius: 4px; margin: 2px 4px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div style="padding: 24px 12px;">
        <div class="wrapper">
            <div class="header">
                <h1>{{ $settings->school_name ?: 'TuCardex Escolar' }}</h1>
                <p>Aviso de Colegiatura y Pagos Escolares</p>
            </div>
            <div class="body">
                <div class="greeting">
                    Estimado(a) {{ $student->guardian_name ?: ($student->first_name ? 'Familia ' . $student->last_name : 'Padre / Tutor') }},
                </div>
                <div class="intro">
                    Le recordamos que se encuentra disponible para pago la siguiente colegiatura de su hijo(a) <strong>{{ $student->full_name }}</strong>.
                </div>

                <div class="card-details">
                    <div class="row-detail">
                        <span class="label">Alumno:</span>
                        <span class="value">{{ $student->full_name }}</span>
                    </div>
                    @if($student->course)
                    <div class="row-detail">
                        <span class="label">Nivel y Grado:</span>
                        <span class="value">{{ $student->course->name }}</span>
                    </div>
                    @endif
                    <div class="row-detail">
                        <span class="label">Concepto:</span>
                        <span class="value">{{ $payment->concept ?: 'Colegiatura' }} {{ $payment->period ? '· ' . $payment->period : '' }}</span>
                    </div>
                    <div class="row-detail">
                        <span class="label">Fecha Límite de Pago:</span>
                        <span class="value" style="color: #b91c1c;">{{ $payment->due_date ? $payment->due_date->format('d/m/Y') : 'Próximamente' }}</span>
                    </div>
                    <div class="row-detail total-row">
                        <span class="label" style="color: #047857;">Total a Pagar:</span>
                        <span class="value">${{ number_format((float) $payment->amount, 2) }} {{ $payment->currency ?: 'MXN' }}</span>
                    </div>
                </div>

                <a href="{{ $checkoutUrl }}" class="btn-pay" target="_blank">
                    👉 PAGAR COLEGIATURA EN LÍNEA
                </a>

                <div class="methods">
                    Métodos aceptados de forma inmediata:<br>
                    <span>🏦 Transferencia SPEI</span>
                    <span>🏪 OXXO en efectivo</span>
                    <span>💳 Tarjeta Débito / Crédito</span>
                </div>

                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 0;">
                    <em>Nota fiscal:</em> Al completar su pago en línea, el sistema generará y enviará su recibo y su <strong>Factura SAT CFDI 4.0 con Complemento IEDU</strong> para su deducción personal en la declaración anual.
                </p>
            </div>
            <div class="footer">
                Este mensaje fue emitido automáticamente por la plataforma de gestión escolar de {{ $settings->school_name ?: 'su colegio' }}.<br>
                Si ya realizó este pago recientemente en caja, por favor haga caso omiso de este correo.
            </div>
        </div>
    </div>
</body>
</html>
