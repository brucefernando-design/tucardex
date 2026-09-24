@extends('layouts.app')
@section('title', 'Facturación Electrónica SAT')

@section('content')
<div class="page-head">
    <div>
        <h1>Facturación Electrónica SAT</h1>
        <div class="breadcrumb-mini">Comprobantes Fiscales Digitales por Internet (CFDI 4.0) · Complemento IEDU</div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="fe-chip-mini {{ $settings->enabled ? 'is-on' : 'is-off' }}">
            <i class="bi bi-{{ $settings->enabled ? 'check-circle-fill' : 'slash-circle' }}"></i> 
            {{ $settings->enabled ? 'Timbrado Activo (Facturapi SAT)' : 'Timbrado Deshabilitado' }}
        </span>
        @if(auth()->user()?->isAdmin())
            <a href="{{ route('facturacion.configuracion') }}" class="btn btn-brand btn-icon"><i class="bi bi-gear"></i> Configuración SAT</a>
        @endif
    </div>
</div>

<div class="stats-row">
    <div class="stat-card bg-green"><div class="label">Timbradas / Aceptadas</div><div class="value">{{ $summary['aceptados'] }}</div><i class="bi bi-check-circle icon"></i></div>
    <div class="stat-card bg-orange"><div class="label">Pendientes</div><div class="value">{{ $summary['pendientes'] }}</div><i class="bi bi-hourglass-split icon"></i></div>
    <div class="stat-card bg-red"><div class="label">Con Error / Rechazadas</div><div class="value">{{ $summary['rechazados'] }}</div><i class="bi bi-x-octagon icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Facturado Total</div><div class="value">{{ $settings->moneda ?: '$' }} {{ number_format($summary['total'], 2) }}</div><i class="bi bi-wallet2 icon"></i></div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por Folio, UUID SAT, Tutor o RFC..."></div>
        <div class="col-md-3"><select name="estado" class="form-select"><option value="">Todo estado</option>@foreach(['pendiente','aceptado','anulado','error'] as $st)<option value="{{ $st }}" @selected(request('estado')==$st)>{{ ucfirst($st) }}</option>@endforeach</select></div>
        <div class="col-md-3 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </form>

    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr>
            <th class="ps-3">Folio</th>
            <th>Tutor / Alumno</th>
            <th>RFC</th>
            <th>Fecha Emisión</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Folio Fiscal (UUID SAT)</th>
            <th class="text-end pe-3">Comprobantes</th>
        </tr></thead>
        <tbody>
        @forelse($invoices as $inv)
            <tr>
                <td class="ps-3"><strong>{{ $inv->full_number }}</strong></td>
                <td>{{ $inv->client_razon_social }}</td>
                <td><code class="text-muted small">{{ $inv->client_num_doc ?: 'XAXX010101000' }}</code></td>
                <td>{{ optional($inv->fecha_emision)->format('d/m/Y H:i') }}</td>
                <td><strong>${{ number_format($inv->total, 2) }} MXN</strong></td>
                <td><span class="badge-soft {{ $inv->badgeClass() }}">{{ ucfirst($inv->estado) }}</span></td>
                <td>
                    @if($inv->hash)
                        <span class="small font-monospace text-muted" title="{{ $inv->hash }}">{{ substr($inv->hash, 0, 13) }}...</span>
                        @if($inv->ticket)
                            <a href="{{ $inv->ticket }}" target="_blank" class="ms-1 text-primary" title="Verificar en SAT"><i class="bi bi-patch-check"></i></a>
                        @endif
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td class="text-end pe-3">
                    <a href="{{ route('facturacion.show', $inv) }}" class="btn btn-sm btn-light" title="Detalle"><i class="bi bi-eye"></i></a>
                    @if($inv->pdf_path)
                        <a href="{{ route('facturacion.pdf', $inv) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Descargar PDF CFDI 4.0"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                    @endif
                    @if($inv->xml_path)
                        <a href="{{ route('facturacion.xml', $inv) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar XML SAT"><i class="bi bi-filetype-xml"></i> XML</a>
                    @endif
                    @if(in_array($inv->estado, ['pendiente', 'error']))
                        <form action="{{ route('facturacion.reenviar', $inv) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success btn-icon" title="Reintentar Timbrado SAT"><i class="bi bi-send"></i></button>
                        </form>
                    @endif
                    @if($inv->estado === 'aceptado')
                        <form action="{{ route('facturacion.anular', $inv) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar este CFDI ante el SAT?')">
                            @csrf
                            <button class="btn btn-sm btn-light text-danger" title="Cancelar ante SAT"><i class="bi bi-x-circle"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8"><div class="empty-state"><i class="bi bi-receipt-cutoff"></i><p>Sin comprobantes fiscales emitidos aún. Se timbrarán automáticamente al registrar pagos de colegiaturas.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $invoices->links() }}
</div></div>

<style>
.fe-chip-mini{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:8px 14px;border-radius:30px}
.fe-chip-mini.is-on{background:var(--brand-soft);color:var(--brand-ink)}
.fe-chip-mini.is-off{background:#fee2e2;color:#991b1b}
</style>
@endsection
