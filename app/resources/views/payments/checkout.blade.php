@extends('layouts.checkout')
@section('title', 'Pagar Colegiatura')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">Pagar Colegiatura en Línea</h2>
        <div class="text-muted small">Liquidación oficial y segura de colegiaturas escolares</div>
    </div>
    @auth
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver al Portal
    </a>
    @endauth
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Resumen de la Colegiatura -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-header bg-dark text-white py-3" style="border-radius: 16px 16px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small text-uppercase tracking-wider text-success fw-bold">
                        <i class="bi bi-shield-check me-1"></i> Resumen de Cuenta
                    </span>
                    <span class="badge bg-warning text-dark">{{ ucfirst($payment->status) }}</span>
                </div>
                <h4 class="mb-0 mt-2 text-white">{{ $payment->concept }}</h4>
                <div class="text-white-50 small">{{ $setting->school_name ?? 'TuKardex' }}</div>
            </div>

            <div class="card-body p-4">
                <!-- Tarjeta del alumno -->
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                    <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #16a34a, #15803d); color:#fff; display:flex; align-items:center; justify-content:center; font-size: 24px; font-weight: bold;">
                        {{ method_exists($student, 'initials') ? $student->initials() : 'AL' }}
                    </div>
                    <div>
                        <strong class="d-block" style="font-size: 15px;">{{ $student->first_name }} {{ $student->last_name }}</strong>
                        <div class="small text-muted">
                            Matrícula: <strong>{{ $student->code }}</strong> · {{ optional($student->course)->name }} "{{ optional($student->course)->section }}"
                        </div>
                    </div>
                </div>

                <!-- Desglose de importes -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Concepto</span>
                        <strong>{{ $payment->concept }}</strong>
                    </div>
                    @if($payment->period)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Periodo escolar</span>
                        <span>{{ $payment->period }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Fecha límite</span>
                        <span class="{{ $payment->due_date && $payment->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                            {{ $payment->due_date ? $payment->due_date->format('d/m/Y') : 'Inmediata' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-3 fs-5 mt-2 bg-light px-3 rounded">
                        <span class="fw-bold">Total a liquidar:</span>
                        <strong class="text-success">${{ number_format($payment->amount, 2) }} MXN</strong>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-info-circle fs-5"></i>
                    <span>Al acreditarse tu pago, tu Recibo Oficial con validez fiscal se generará de inmediato.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Métodos de Pago Disponibles -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-wallet2 text-primary me-2"></i> Selecciona tu método de pago</h5>
            </div>

            <div class="card-body p-4">
                <!-- Opción 1: Mercado Pago -->
                <div class="p-4 rounded-3 border mb-4" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: #009ee3; color:#fff; display:flex; align-items:center; justify-content:center; font-size: 26px;">
                                <i class="bi bi-credit-card-2-front"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Mercado Pago México</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Acreditación Inmediata</span>
                            </div>
                        </div>
                    </div>

                    <p class="small text-secondary mb-3">
                        Acepta <strong>Tarjetas de Débito y Crédito</strong> (Visa, Mastercard, AMEX), <strong>Transferencia SPEI en tiempo real</strong> o <strong>Efectivo en OXXO</strong> con código de barras.
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-white text-dark border px-2 py-1"><i class="bi bi-credit-card me-1"></i> Tarjetas</span>
                        <span class="badge bg-white text-dark border px-2 py-1"><i class="bi bi-lightning-charge me-1"></i> SPEI</span>
                        <span class="badge bg-white text-dark border px-2 py-1"><i class="bi bi-shop me-1"></i> OXXO Pay</span>
                    </div>

                    <form action="{{ route('parent.payments.mercadopago', $payment) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-6 shadow-sm" style="border-radius: 12px;">
                            <i class="bi bi-lock-fill me-1"></i> Pagar ${{ number_format($payment->amount, 2) }} MXN con Mercado Pago
                        </button>
                    </form>

                    
                </div>

                <!-- Opción 2: Transferencia Interbancaria SPEI Directa -->
                <div class="p-4 rounded-3 border" style="background: #f8fafc;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #334155; color:#fff; display:flex; align-items:center; justify-content:center; font-size: 24px;">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">Transferencia Interbancaria SPEI</h6>
                            <small class="text-muted">Transfiere directo desde la aplicación de tu banco</small>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded border mb-3">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <small class="text-muted d-block" style="font-size: 11px;">BANCO RECEPTOR</small>
                                <strong>{{ $setting->spei_bank ?? 'BBVA México' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block" style="font-size: 11px;">BENEFICIARIO</small>
                                <strong>{{ $setting->spei_beneficiary ?? ($setting->school_name ?? 'Colegio San Martín') }}</strong>
                            </div>
                            <div class="col-12 mt-2 pt-2 border-top">
                                <small class="text-muted d-block" style="font-size: 11px;">CLABE INTERBANCARIA (18 DÍGITOS)</small>
                                <div class="d-flex align-items-center justify-content-between mt-1 p-2 rounded bg-light border">
                                    <span class="font-monospace fs-6 fw-bold text-primary" id="clabeText">{{ $setting->spei_clabe ?? '012180001234567890' }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-3" onclick="copyClabe()">
                                        <i class="bi bi-copy me-1"></i> Copiar CLABE
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <small class="text-muted d-block" style="font-size: 11px;">CONCEPTO SUGERIDO</small>
                                <span class="font-monospace text-dark">{{ $student->code }} {{ Str::upper($student->first_name) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Subir Comprobante -->
                    <div class="border-top pt-3">
                        <label class="form-label small fw-bold">¿Ya hiciste tu transferencia? Sube tu comprobante:</label>
                        <form action="{{ route('parent.payments.voucher', $payment) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="voucher" accept="image/*,.pdf" class="form-control" required>
                                <button class="btn btn-outline-dark" type="submit">
                                    <i class="bi bi-upload me-1"></i> Enviar
                                </button>
                            </div>
                            <div class="form-text">Formatos permitidos: JPG, PNG o PDF (máx 5MB).</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyClabe() {
    const text = document.getElementById('clabeText').innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        alert('CLABE interbancaria copiada: ' + text);
    });
}
</script>
@endsection
