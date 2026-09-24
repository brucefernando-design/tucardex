@extends('layouts.app')
@section('title', 'Pasarelas de Pago y Cobranza')

@section('content')
<div class="page-head">
    <div>
        <h1>Pasarelas de Pago y Cobranza</h1>
        <div class="breadcrumb-mini">Configura los métodos de cobro para que los padres de familia paguen colegiaturas directamente a tu colegio</div>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-cash-stack"></i> Ver Colegiaturas</a>
</div>

{{-- Banner Informativo --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; border:none; border-radius:14px;">
    <div class="card-body p-4 d-flex align-items-center gap-3">
        <div style="font-size: 38px; color: #38bdf8;"><i class="bi bi-shield-check"></i></div>
        <div>
            <h5 class="mb-1 text-white">El dinero de las colegiaturas entra 100% directo a tu colegio</h5>
            <p class="mb-0 text-slate-300 small">TuCardex no cobra comisiones ni retiene tus fondos. Los pagos por SPEI, tarjeta o Mercado Pago se acreditan directamente en la cuenta bancaria de tu institución.</p>
        </div>
    </div>
</div>

<form action="{{ route('payments.gateways.save') }}" method="POST">@csrf

    {{-- Transferencia SPEI (CLABE) --}}
    <div class="card mb-4 shadow-sm border-0" style="border-radius:12px;">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <span class="fw-bold d-flex align-items-center gap-2" style="font-size: 16px;">
                <i class="bi bi-bank text-primary" style="font-size: 20px;"></i> 
                Transferencia Interbancaria SPEI (México)
            </span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" role="switch" name="spei_enabled" value="1" @checked($settings->spei_enabled) id="spei_switch">
                <label class="form-check-label small fw-semibold" for="spei_switch">Habilitado</label>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small">Los padres de familia verán estos datos bancarios en su portal para realizar transferencias por SPEI desde su app bancaria.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Banco Receptor <span class="text-danger">*</span></label>
                    <input name="spei_bank" value="{{ old('spei_bank', $settings->spei_bank) }}" class="form-control" placeholder="Ej. BBVA, Santander, Banorte">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CLABE Interbancaria (18 dígitos) <span class="text-danger">*</span></label>
                    <input name="spei_clabe" value="{{ old('spei_clabe', $settings->spei_clabe) }}" class="form-control" maxlength="18" placeholder="012180001234567890">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Beneficiario (Titular de la cuenta)</label>
                    <input name="spei_beneficiary" value="{{ old('spei_beneficiary', $settings->spei_beneficiary) }}" class="form-control" placeholder="Nombre o Razón Social del Colegio">
                </div>
                <div class="col-12">
                    <label class="form-label">Instrucciones de Pago / Concepto de Transferencia</label>
                    <textarea name="spei_instructions" class="form-control" rows="2" placeholder="Ej: En concepto de pago poner NOMBRE DEL ALUMNO y GRADO. Enviar comprobante a finanzas@colegio.edu.mx">{{ old('spei_instructions', $settings->spei_instructions) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Mercado Pago --}}
    <div class="card mb-4 shadow-sm border-0" style="border-radius:12px;">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <span class="fw-bold d-flex align-items-center gap-2" style="font-size: 16px;">
                <i class="bi bi-wallet2 text-info" style="font-size: 20px;"></i> 
                Mercado Pago México (Tarjetas y OXXO)
            </span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" role="switch" name="mercadopago_enabled" value="1" @checked($settings->mercadopago_enabled) id="mp_switch">
                <label class="form-check-label small fw-semibold" for="mp_switch">Habilitado</label>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small">Permite cobrar colegiaturas con tarjeta de crédito, débito y efectivo en tiendas OXXO. Las credenciales se obtienen en tu panel de Desarrolladores de Mercado Pago.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Public Key</label>
                    <input name="mercadopago_public_key" value="{{ old('mercadopago_public_key', $settings->mercadopago_public_key) }}" class="form-control" placeholder="APP_USR-••••••••••••">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Access Token</label>
                    <input type="password" name="mercadopago_access_token" value="{{ old('mercadopago_access_token', $settings->mercadopago_access_token) }}" class="form-control" placeholder="APP_USR-••••••••••••">
                </div>
            </div>
        </div>
    </div>

    {{-- Stripe --}}
    <div class="card mb-4 shadow-sm border-0" style="border-radius:12px;">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <span class="fw-bold d-flex align-items-center gap-2" style="font-size: 16px;">
                <i class="bi bi-credit-card text-success" style="font-size: 20px;"></i> 
                Stripe (Tarjetas Internacionales y Nacionales)
            </span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" role="switch" name="stripe_enabled" value="1" @checked($settings->stripe_enabled) id="stripe_switch">
                <label class="form-check-label small fw-semibold" for="stripe_switch">Habilitado</label>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small">Acepta pagos directos con tarjetas Visa, MasterCard y American Express conectando las llaves API de tu cuenta Stripe.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Publishable Key</label>
                    <input name="stripe_public_key" value="{{ old('stripe_public_key', $settings->stripe_public_key) }}" class="form-control" placeholder="pk_live_••••••••••••">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Secret Key</label>
                    <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', $settings->stripe_secret_key) }}" class="form-control" placeholder="sk_live_••••••••••••">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center py-3">
        <a href="{{ route('payments.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver a Colegiaturas</a>
        <button class="btn btn-brand px-4 py-2"><i class="bi bi-check-lg me-1"></i> Guardar Pasarelas de Pago</button>
    </div>
</form>
@endsection
