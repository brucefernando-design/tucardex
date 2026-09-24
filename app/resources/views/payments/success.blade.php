@extends('layouts.checkout')
@section('title', 'Pago Acreditado')

@section('content')
<div class="container py-4" style="max-width: 650px;">
    <div class="card shadow border-0 text-center" style="border-radius: 20px; overflow:hidden;">
        <div class="py-5 px-4 text-white" style="background: linear-gradient(135deg, #16a34a, #15803d);">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; margin: 0 auto 16px; font-size: 42px;">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 class="mb-1 text-white fw-bold">¡Pago Acreditado con Éxito!</h2>
            <div class="text-white-50">Tu colegiatura ha sido liquidada en el sistema</div>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="p-3 rounded-3 border mb-4 text-start" style="background: #f8fafc;">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Folio de Recibo</span>
                    <strong class="font-monospace text-primary">{{ $payment->invoice_number }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Alumno</span>
                    <strong>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Concepto</span>
                    <span>{{ $payment->concept }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Método</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ ucfirst($payment->method ?? 'En línea') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Fecha de pago</span>
                    <span>{{ $payment->paid_date ? $payment->paid_date->format('d/m/Y H:i') : now()->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex justify-content-between py-3 fs-5 mt-2 bg-white px-3 rounded border">
                    <span class="fw-bold">Monto Liquidado:</span>
                    <strong class="text-success">${{ number_format($payment->amount, 2) }} MXN</strong>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('parent.payments.receipt', $payment) }}" target="_blank" class="btn btn-primary py-3 fw-bold fs-6 rounded-3 shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Descargar Recibo Oficial en PDF
                </a>
                @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary py-2 rounded-3">
                    <i class="bi bi-house me-1"></i> Volver al Portal de Padres
                </a>
                @else
                <div class="text-muted small mt-2">
                    <i class="bi bi-check-circle text-success me-1"></i> Puedes cerrar esta ventana con seguridad. Una copia de tu comprobante ha sido registrada.
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
