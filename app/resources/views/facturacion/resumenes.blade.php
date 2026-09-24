@extends('layouts.app')
@section('title', 'Resúmenes Diarios')

@section('content')
<div class="page-head">
    <div><h1>Resúmenes Diarios de Boletas</h1><div class="breadcrumb-mini">Envío agrupado de boletas a SUNAT (producción)</div></div>
    <a href="{{ route('facturacion.index') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-receipt"></i> Comprobantes</a>
</div>

@unless($settings->boletas_por_resumen)
    <div class="alert alert-warning"><i class="bi bi-info-circle me-2"></i>El modo <strong>Boletas por Resumen Diario</strong> está desactivado. Actívalo en <a href="{{ route('facturacion.configuracion') }}">Configuración</a> para que las boletas queden pendientes y se informen por resumen.</div>
@endunless

<div class="grid-2">
    {{-- Boletas pendientes por día --}}
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-calendar-week"></i> Boletas pendientes por día</span></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th class="ps-3">Fecha</th><th>Boletas</th><th>Monto</th><th class="text-end pe-3">Acción</th></tr></thead>
                <tbody>
                @forelse($pendientes as $row)
                    <tr>
                        <td class="ps-3"><strong>{{ \Carbon\Carbon::parse($row->dia)->format('d/m/Y') }}</strong></td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $settings->moneda ?: 'PEN' }} {{ number_format($row->monto,2) }}</td>
                        <td class="text-end pe-3">
                            <form action="{{ route('facturacion.resumenes.generar') }}" method="POST">@csrf
                                <input type="hidden" name="fecha" value="{{ $row->dia }}">
                                <button class="btn btn-sm btn-brand btn-icon"><i class="bi bi-send"></i> Enviar resumen</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-check2-circle"></i><p>No hay boletas pendientes de resumen.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Generar por fecha manual --}}
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-calendar-plus"></i> Generar por fecha</span></div>
        <div class="card-body">
            <form action="{{ route('facturacion.resumenes.generar') }}" method="POST" class="row g-2">@csrf
                <div class="col-8"><input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="col-4 d-grid"><button class="btn btn-brand btn-icon"><i class="bi bi-send"></i> Enviar</button></div>
            </form>
            <p class="text-muted small mt-3 mb-0"><i class="bi bi-info-circle"></i> El resumen agrupa las boletas <strong>pendientes</strong> emitidas en la fecha indicada y devuelve un <strong>ticket</strong>. Luego consulta el estado para obtener el CDR de SUNAT.</p>
            <p class="text-muted small mt-2 mb-0"><i class="bi bi-robot"></i> <strong>Automático:</strong> con el cron activo, el resumen del día anterior se envía a la 01:00 y los tickets se consultan cada 30 min.</p>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><span class="title"><i class="bi bi-list-check"></i> Resúmenes enviados</span></div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="ps-3">Identificador</th><th>Fecha docs.</th><th>Boletas</th><th>Total</th><th>Ticket</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
            <tbody>
            @forelse($summaries as $s)
                <tr>
                    <td class="ps-3"><strong>{{ $s->identifier }}</strong></td>
                    <td>{{ optional($s->fecha_generacion)->format('d/m/Y') }}</td>
                    <td>{{ $s->invoices_count }}</td>
                    <td>{{ $settings->moneda ?: 'PEN' }} {{ number_format($s->total,2) }}</td>
                    <td class="small text-muted text-break" style="max-width:160px">{{ $s->ticket ?: '—' }}</td>
                    <td><span class="badge-soft {{ $s->badgeClass() }}">{{ ucfirst($s->estado) }}</span>@if($s->sunat_code)<div class="text-muted small">{{ $s->sunat_code }}</div>@endif</td>
                    <td class="text-end pe-3">
                        @if($s->ticket && in_array($s->estado, ['ticket','enviando','observado']))
                            <form action="{{ route('facturacion.resumenes.consultar', $s) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success btn-icon"><i class="bi bi-arrow-repeat"></i> Consultar</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inboxes"></i><p>Aún no se han enviado resúmenes.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $summaries->links() }}</div>
@endsection
