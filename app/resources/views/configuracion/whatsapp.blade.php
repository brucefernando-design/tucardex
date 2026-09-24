@extends('layouts.app')
@section('title', 'WhatsApp & Cobranza Automatizada')

@section('content')
<div class="page-head">
    <div>
        <h1>WhatsApp & Cobranza Automatizada</h1>
        <div class="breadcrumb-mini">Recordatorios de colegiaturas con enlaces de pago en línea y motor Anti-Baneo</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-icon">
            <i class="bi bi-wallet2"></i> Ver Colegiaturas y Caja
        </a>
        <form action="{{ route('configuracion.whatsapp.disparar') }}" method="POST" onsubmit="return confirm('¿Deseas iniciar el envío de recordatorios de cobranza para todos los pagos pendientes del día? Se enviarán en segundo plano con pausas aleatorias humanas.');">
            @csrf
            <button type="submit" class="btn btn-brand btn-icon">
                <i class="bi bi-send-check-fill"></i> Disparar Cobranza del Día
            </button>
        </form>
    </div>
</div>

{{-- Banner WhatsApp Escolar --}}
<div class="wa-banner mb-4">
    <div class="wa-banner-main">
        <div class="wa-banner-icon"><i class="bi bi-whatsapp"></i></div>
        <div>
            <div class="wa-banner-title">
                Cobranza Inteligente <span class="wa-tag">WhatsApp + Correo</span>
            </div>
            <div class="wa-banner-sub">
                Envía recordatorios amigables a los padres de familia con su botón de pago directo a <strong>SPEI, OXXO y Tarjeta</strong>, reduciendo la cartera vencida en piloto automático.
            </div>
        </div>
    </div>
    <div class="wa-banner-right">
        <div class="wa-stat-pill">
            <span class="num">{{ $vencidosCount }}</span>
            <span class="lbl">Pagos Vencidos</span>
        </div>
        <div class="wa-stat-pill">
            <span class="num">{{ $pendientesCount }}</span>
            <span class="lbl">Por Cobrar</span>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Columna Izquierda: Vinculación QR de WhatsApp --}}
    <div class="col-lg-5">
        <div class="card h-100 {{ ($statusData['status'] ?? '') === 'connected' ? 'border-success' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="title">
                    <i class="bi bi-qr-code text-success fs-5"></i>
                    <strong>Conexión con WhatsApp</strong>
                </span>
                <span id="wa-status-badge" class="badge {{ ($statusData['status'] ?? '') === 'connected' ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ ($statusData['status'] ?? '') === 'connected' ? '🟢 Conectado' : '🟡 Esperando Escaneo' }}
                </span>
            </div>
            <div class="card-body text-center p-4">
                @if(($statusData['status'] ?? '') === 'connected')
                    <div class="py-4">
                        <div class="wa-success-circle mx-auto mb-3">
                            <i class="bi bi-check-lg text-success" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="fw-bold text-success mb-1">WhatsApp de la Escuela Conectado</h5>
                        <p class="text-muted small mb-3">
                            Línea vinculada: <strong>+{{ $statusData['phone'] ?? 'Activo' }}</strong><br>
                            Los recordatorios se envían automáticamente desde este número.
                        </p>
                        
                        <hr class="my-4">

                        {{-- Prueba de envío --}}
                        <div class="text-start">
                            <label class="form-label small fw-bold"><i class="bi bi-chat-left-dots text-success"></i> Enviar mensaje de prueba:</label>
                            <form action="{{ route('configuracion.whatsapp.test') }}" method="POST" class="d-flex flex-column gap-2">
                                @csrf
                                <input type="text" name="phone" class="form-control form-control-sm" placeholder="Teléfono a 10 dígitos (ej: 8671234567)" required>
                                <textarea name="message" class="form-control form-control-sm" rows="2" required>Prueba de conexión exitosa desde TuCardex Escolar 🎓</textarea>
                                <button type="submit" class="btn btn-outline-success btn-sm mt-1">
                                    <i class="bi bi-send"></i> Enviar prueba a mi WhatsApp
                                </button>
                            </form>
                        </div>

                        <form action="{{ route('configuracion.whatsapp.logout') }}" method="POST" class="mt-4" onsubmit="return confirm('¿Deseas desconectar el WhatsApp de la escuela?');">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger btn-sm text-decoration-none">
                                <i class="bi bi-power"></i> Desconectar sesión de WhatsApp
                            </button>
                        </form>
                    </div>
                @else
                    <div id="qr-container">
                        <div class="mb-3 text-start bg-light p-3 rounded-3 small">
                            <strong class="d-block mb-1 text-dark">Pasos para vincular:</strong>
                            <ol class="ps-3 mb-0 text-muted">
                                <li>Abre <strong>WhatsApp</strong> en el celular de la escuela.</li>
                                <li>Toca <strong>Menú (⋮)</strong> o <strong>Configuración</strong>.</li>
                                <li>Selecciona <strong>Dispositivos vinculados</strong> y luego <strong>Vincular un dispositivo</strong>.</li>
                                <li>Apunta la cámara de tu celular hacia este código QR.</li>
                            </ol>
                        </div>

                        <div class="wa-qr-box mx-auto my-3">
                            <img id="wa-qr-image" src="{{ $qrData['qr'] ?? '' }}" alt="Código QR WhatsApp" class="img-fluid rounded" style="max-width: 250px; display: {{ !empty($qrData['qr']) ? 'block' : 'none' }};">
                            <div id="wa-qr-loading" style="display: {{ empty($qrData['qr']) ? 'block' : 'none' }}; padding: 60px 20px;">
                                <div class="spinner-border text-success mb-2" role="status"></div>
                                <div class="small text-muted">Generando código QR...</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fetchQrCode();">
                                <i class="bi bi-arrow-clockwise"></i> Refrescar Código QR
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Configuración de Intervalos y Reglas --}}
    <div class="col-lg-7">
        <form action="{{ route('configuracion.whatsapp.guardar') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <span class="title"><i class="bi bi-shield-check text-primary"></i> <strong>Motor Anti-Baneo (Jitter Humano Aleatorio)</strong></span>
                    <span class="text-muted small">Pausas impredecibles entre mensajes para emular comportamiento de una persona</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 mb-3 small d-flex gap-2">
                        <i class="bi bi-info-circle-fill fs-5 mt-1 text-primary"></i>
                        <div>
                            <strong>¿Por qué funciona el Anti-Baneo?</strong> Al enviar los recordatorios con pausas variables (por ejemplo: 45s, 58s, 1m 10s y 1m 58s), los servidores de WhatsApp no detectan patrones robóticos, protegiendo al 100% el número del colegio.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Pausa Mínima entre Mensajes</label>
                            <div class="input-group">
                                <input type="number" name="reminder_min_delay" value="{{ old('reminder_min_delay', $settings->reminder_min_delay ?: 45) }}" class="form-control" min="15" max="300" required>
                                <span class="input-group-text">segundos</span>
                            </div>
                            <div class="form-text">Recomendado: 45 segundos.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Pausa Máxima entre Mensajes</label>
                            <div class="input-group">
                                <input type="number" name="reminder_max_delay" value="{{ old('reminder_max_delay', $settings->reminder_max_delay ?: 118) }}" class="form-control" min="30" max="600" required>
                                <span class="input-group-text">segundos (1m 58s)</span>
                            </div>
                            <div class="form-text">Recomendado: 118 segundos (~2 min).</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <span class="title"><i class="bi bi-clock-history text-success"></i> <strong>Reglas y Horarios de Cobranza</strong></span>
                    <span class="text-muted small">Programación de envíos automáticos</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch p-3 border rounded bg-light">
                                <input class="form-check-input" type="checkbox" role="switch" id="whatsapp_enabled" name="whatsapp_enabled" value="1" @checked($settings->whatsapp_enabled)>
                                <label class="form-check-label fw-bold ms-2" for="whatsapp_enabled">
                                    Enviar por WhatsApp
                                    <span class="d-block text-muted small fw-normal">Mensaje con saludo amigable y botón de pago.</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch p-3 border rounded bg-light">
                                <input class="form-check-input" type="checkbox" role="switch" id="email_reminders_enabled" name="email_reminders_enabled" value="1" @checked($settings->email_reminders_enabled)>
                                <label class="form-check-label fw-bold ms-2" for="email_reminders_enabled">
                                    Enviar por Correo Electrónico
                                    <span class="d-block text-muted small fw-normal">Plantilla formal con logotipo y desglose de cuotas.</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Recordatorio Preventivo</label>
                            <div class="input-group">
                                <input type="number" name="reminder_days_before" value="{{ old('reminder_days_before', $settings->reminder_days_before ?: 3) }}" class="form-control" min="1" max="15" required>
                                <span class="input-group-text">días antes</span>
                            </div>
                            <div class="form-text">Avisa antes de la fecha límite.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Aviso de Mora / Vencido</label>
                            <div class="input-group">
                                <input type="number" name="reminder_days_after" value="{{ old('reminder_days_after', $settings->reminder_days_after ?: 3) }}" class="form-control" min="1" max="15" required>
                                <span class="input-group-text">días después</span>
                            </div>
                            <div class="form-text">Re-notifica a los que no pagaron.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Hora de Envío Matutino</label>
                            <input type="time" name="reminder_hour" value="{{ old('reminder_hour', $settings->reminder_hour ?: '08:30') }}" class="form-control" required>
                            <div class="form-text">Horario recomendado: 08:30 AM.</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end py-3">
                    <button type="submit" class="btn btn-brand btn-icon">
                        <i class="bi bi-check-lg"></i> Guardar Configuración de Cobranza
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
.wa-banner{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;
    background:linear-gradient(135deg,#065f46,#047857);color:#fff;border-radius:var(--radius);
    padding:22px 26px;box-shadow:var(--shadow)}
.wa-banner-main{display:flex;align-items:center;gap:18px}
.wa-banner-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.16);
    display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0}
.wa-banner-title{font-size:22px;font-weight:800;letter-spacing:-.3px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.wa-tag{font-size:13px;font-weight:600;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px}
.wa-banner-sub{opacity:.92;font-size:13px;margin-top:4px;max-width:640px}
.wa-banner-right{display:flex;gap:12px}
.wa-stat-pill{background:rgba(255,255,255,.14);padding:10px 18px;border-radius:12px;text-align:center;min-width:100px}
.wa-stat-pill .num{display:block;font-size:22px;font-weight:800;line-height:1}
.wa-stat-pill .lbl{display:block;font-size:11px;opacity:.9;margin-top:4px}
.wa-success-circle{width:90px;height:90px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center}
.wa-qr-box{padding:12px;background:#fff;border:2px dashed #047857;border-radius:12px;display:inline-block}
</style>

<script>
let pollingInterval = null;

function fetchQrCode() {
    const img = document.getElementById('wa-qr-image');
    const loading = document.getElementById('wa-qr-loading');
    
    if (loading) loading.style.display = 'block';
    if (img) img.style.display = 'none';

    fetch("{{ route('configuracion.whatsapp.qr') }}")
        .then(res => res.json())
        .then(data => {
            if (data.status === 'connected') {
                window.location.reload();
                return;
            }
            if (data.qr) {
                if (img) {
                    img.src = data.qr;
                    img.style.display = 'block';
                }
                if (loading) loading.style.display = 'none';
            }
        })
        .catch(err => console.error('Error fetching QR:', err));
}

function pollStatus() {
    fetch("{{ route('configuracion.whatsapp.status') }}")
        .then(res => res.json())
        .then(data => {
            if (data.status === 'connected') {
                clearInterval(pollingInterval);
                window.location.reload();
            } else if (data.status === 'scan_qr' && data.qr) {
                const img = document.getElementById('wa-qr-image');
                if (img && img.src !== data.qr) {
                    img.src = data.qr;
                    img.style.display = 'block';
                    const loading = document.getElementById('wa-qr-loading');
                    if (loading) loading.style.display = 'none';
                }
            }
        })
        .catch(err => console.error('Error polling status:', err));
}

document.addEventListener('DOMContentLoaded', () => {
    const isConnected = "{{ ($statusData['status'] ?? '') === 'connected' ? 'yes' : 'no' }}";
    if (isConnected === 'no') {
        pollingInterval = setInterval(pollStatus, 3500);
        // Si no hay QR al cargar, solicitarlo
        const img = document.getElementById('wa-qr-image');
        if (img && !img.src) {
            fetchQrCode();
        }
    }
});
</script>
@endpush
