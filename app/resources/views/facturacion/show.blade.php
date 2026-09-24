@extends('layouts.app')
@section('title', 'CFDI '.$invoice->full_number)

@section('content')
<div class="page-head">
    <div>
        <h1>CFDI 4.0 · {{ $invoice->full_number }}</h1>
        <div class="breadcrumb-mini">Comprobante Fiscal Digital por Internet · Emitido el {{ optional($invoice->fecha_emision)->format('d/m/Y H:i') }}</div>
    </div>
    <a href="{{ route('facturacion.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-receipt"></i> Datos del Comprobante Fiscal</span>
            <span class="badge-soft {{ $invoice->badgeClass() }} ms-auto">{{ ucfirst($invoice->estado) }}</span>
        </div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr><th class="text-muted" style="width:42%">Serie · Folio</th><td>{{ $invoice->full_number }}</td></tr>
                <tr><th class="text-muted">Receptor / Tutor</th><td>{{ $invoice->client_razon_social }}</td></tr>
                <tr><th class="text-muted">RFC Receptor</th><td><code>{{ $invoice->client_num_doc ?: 'XAXX010101000' }}</code></td></tr>
                <tr><th class="text-muted">Uso de CFDI</th><td>D10 — Pagos por servicios educativos (colegiaturas)</td></tr>
                <tr><th class="text-muted">Moneda</th><td>MXN (Pesos Mexicanos)</td></tr>
                <tr><th class="text-muted">Impuestos</th><td>IVA Exento (Art. 15 Fracc. IV LIVA)</td></tr>
                <tr><th class="text-muted">Total</th><td><strong>${{ number_format($invoice->total, 2) }} MXN</strong></td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-shield-check"></i> Certificación SAT (Timbrado Digital)</span></div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><th class="text-muted" style="width:42%">Estado SAT</th><td><span class="badge-soft {{ $invoice->badgeClass() }}">{{ ucfirst($invoice->estado) }}</span></td></tr>
                <tr><th class="text-muted">Folio Fiscal (UUID)</th><td><span class="font-monospace small text-break">{{ $invoice->hash ?: '—' }}</span></td></tr>
                <tr><th class="text-muted">ID Facturapi</th><td><span class="font-monospace small text-muted">{{ $invoice->sunat_code ?: '—' }}</span></td></tr>
                <tr><th class="text-muted">Complemento</th><td>Instituciones Educativas Privadas (IEDU)</td></tr>
            </table>
            @if($invoice->error_message)
                <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>{{ $invoice->error_message }}</div>
            @endif
            <div class="d-flex gap-2 flex-wrap mt-3">
                @if($invoice->pdf_path)
                    <a href="{{ route('facturacion.pdf', $invoice) }}" target="_blank" class="btn btn-sm btn-brand btn-icon"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF Oficial</a>
                @endif
                @if($invoice->xml_path)
                    <a href="{{ route('facturacion.xml', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-filetype-xml"></i> Descargar XML SAT</a>
                @endif
                @if($invoice->ticket)
                    <a href="{{ $invoice->ticket }}" target="_blank" class="btn btn-sm btn-outline-success btn-icon"><i class="bi bi-patch-check"></i> Verificar en Portal SAT</a>
                @endif
                @if(in_array($invoice->estado, ['pendiente','error']))
                    <form action="{{ route('facturacion.reenviar', $invoice) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-brand btn-icon"><i class="bi bi-send"></i> Reintentar Timbrado</button>
                    </form>
                @endif
                @if($invoice->estado === 'aceptado')
                    <form action="{{ route('facturacion.anular', $invoice) }}" method="POST" onsubmit="return confirm('¿Cancelar este CFDI ante el SAT?')">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger btn-icon"><i class="bi bi-x-circle"></i> Cancelar en SAT</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
