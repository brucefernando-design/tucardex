@extends('layouts.app')
@section('title', 'Pagos y Colegiaturas')

@section('content')
<div class="page-head"><div><h1>Pagos y Colegiaturas</h1><div class="breadcrumb-mini">Cobranzas, inscripciones y colegiaturas</div></div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download"></i> Exportar</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('payments.export', array_merge(request()->only('status'), ['format'=>'csv'])) }}"><i class="bi bi-filetype-csv me-2"></i>Excel / CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('payments.export', array_merge(request()->only('status'), ['format'=>'pdf'])) }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
            </ul>
        </div>
        <a href="{{ route('payments.defaulters') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-exclamation-octagon"></i> Morosos</a>
        <button class="btn btn-outline-secondary btn-icon" data-bs-toggle="modal" data-bs-target="#mGen"><i class="bi bi-calendar-plus"></i> Generar colegiaturas</button>
        <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mPay"><i class="bi bi-plus-lg"></i> Registrar pago</button>
    </div></div>

<div class="stats-row">
    <div class="stat-card bg-green"><div class="label">Cobrado</div><div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($summary['paid'],0) }}</div><i class="bi bi-check-circle icon"></i></div>
    <div class="stat-card bg-orange"><div class="label">Pendiente</div><div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($summary['pending'],0) }}</div><i class="bi bi-hourglass-split icon"></i></div>
    <div class="stat-card bg-red"><div class="label">Vencido</div><div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($summary['overdue'],0) }}</div><i class="bi bi-exclamation-octagon icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Total general</div><div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($summary['paid']+$summary['pending']+$summary['overdue'],0) }}</div><i class="bi bi-wallet2 icon"></i></div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar estudiante..."></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">Todos</option>@foreach(['pendiente','pagado','vencido','anulado'] as $st)<option value="{{ $st }}" @selected(request('status')==$st)>{{ ucfirst($st) }}</option>@endforeach</select></div>
        <div class="col-md-3 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Folio</th><th>Estudiante</th><th>Concepto</th><th>Monto</th><th>Vence</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
        <tbody>
        @forelse($payments as $p)
            <tr>
                <td class="ps-3 text-muted small">{{ $p->invoice_number }}</td>
                <td>{{ optional($p->student)->full_name }}</td>
                <td>{{ $p->concept }}</td>
                <td><strong>{{ $appSettings->currency ?? '$' }} {{ number_format($p->amount,2) }}</strong></td>
                <td>{{ optional($p->due_date)->format('d/m/Y') ?? '—' }}</td>
                <td><span class="badge-soft badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                <td class="text-end pe-3">
                    @if($p->status !== 'pagado')
                        @php
                            $phoneRaw = optional($p->student)->guardian_phone ?: optional($p->student)->phone;
                            $cleanPhone = preg_replace('/\D/', '', $phoneRaw ?? '');
                            if (strlen($cleanPhone) === 10) { $cleanPhone = '521' . $cleanPhone; }
                            elseif (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '52') && !str_starts_with($cleanPhone, '521')) { $cleanPhone = '521' . substr($cleanPhone, 2); }
                            $waMsg = app(\App\Services\WhatsApp\WhatsAppService::class)->buildReminderMessage($p);
                            $waLink = "https://wa.me/{$cleanPhone}?text=" . urlencode($waMsg);
                        @endphp
                        @if($cleanPhone)
                            <a href="{{ $waLink }}" target="_blank" class="btn btn-sm btn-outline-success" title="Enviar recordatorio por WhatsApp Web (1 clic directo)"><i class="bi bi-whatsapp"></i></a>
                        @endif
                        <form action="{{ route('payments.recordar', $p) }}" method="POST" class="d-inline" title="Encolar recordatorio automático (WhatsApp + Correo)">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-bell"></i></button>
                        </form>
                        <form action="{{ route('payments.markPaid', $p) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-success btn-icon"><i class="bi bi-check2"></i> Pagar</button></form>
                    @endif
                    <a href="{{ route('payments.receipt', $p) }}" class="btn btn-sm btn-light" title="Recibo PDF"><i class="bi bi-receipt"></i></a>
                    @if($billing->enabled)
                        @php $cpe = $p->comprobante(); @endphp
                        @if($cpe)
                            <a href="{{ route('facturacion.show', $cpe) }}" class="btn btn-sm btn-light" title="Comprobante {{ $cpe->full_number }} ({{ $cpe->estado }})"><i class="bi bi-receipt-cutoff text-success"></i></a>
                        @else
                            <div class="dropdown d-inline">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" title="Emitir comprobante"><i class="bi bi-receipt-cutoff"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Comprobante Fiscal</h6></li>
                                    <li><form action="{{ route('facturacion.emitir', $p) }}" method="POST">@csrf<input type="hidden" name="tipo_doc" value="03"><button class="dropdown-item"><i class="bi bi-file-text me-2"></i>Boleta / Recibo</button></form></li>
                                    <li><form action="{{ route('facturacion.emitir', $p) }}" method="POST">@csrf<input type="hidden" name="tipo_doc" value="01"><button class="dropdown-item"><i class="bi bi-file-earmark-text me-2"></i>Factura</button></form></li>
                                </ul>
                            </div>
                        @endif
                    @endif
                    <form action="{{ route('payments.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-cash-stack"></i><p>Sin pagos registrados</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $payments->links() }}
</div></div>

<div class="modal fade" id="mPay"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('payments.store') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title">Registrar pago</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Estudiante</label><select name="student_id" class="form-select" required><option value="">Seleccione...</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach</select></div>
            <div class="row g-2">
                <div class="col-7"><label class="form-label">Concepto</label><input name="concept" class="form-control" placeholder="Ej: Colegiatura Marzo" required></div>
                <div class="col-5"><label class="form-label">Monto ({{ $appSettings->currency ?? '$' }} MXN)</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Periodo</label><input name="period" class="form-control" placeholder="Ej: Marzo {{ date('Y') }}"></div>
                <div class="col-6"><label class="form-label">Vencimiento</label><input type="date" name="due_date" class="form-control"></div>
                <div class="col-6"><label class="form-label">Estado</label><select name="status" class="form-select">@foreach(['pendiente','pagado','vencido'] as $st)<option value="{{ $st }}">{{ ucfirst($st) }}</option>@endforeach</select></div>
                <div class="col-6"><label class="form-label">Método</label><select name="method" class="form-select"><option value="">—</option>@foreach(['efectivo','transferencia','tarjeta','qr'] as $m)<option value="{{ $m }}">{{ ucfirst($m) }}</option>@endforeach</select></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand">Guardar pago</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="mGen"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('payments.generate') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title">Generar colegiaturas por curso</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="text-muted small">Crea un pago pendiente para cada estudiante activo del curso. No se duplican si ya existe el mismo concepto y período.</p>
            <div class="mb-2"><label class="form-label">Curso</label><select name="course_id" class="form-select" required><option value="">Seleccione...</option>@foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->name }} "{{ $c->section }}"</option>@endforeach</select></div>
            <div class="row g-2">
                <div class="col-7"><label class="form-label">Concepto</label><input name="concept" class="form-control" value="Colegiatura {{ now()->translatedFormat('F') }}" required></div>
                <div class="col-5"><label class="form-label">Monto ({{ $appSettings->currency ?? '$' }} MXN)</label><input type="number" step="0.01" name="amount" value="{{ optional($appSettings)->tuition_amount ?? 250 }}" required></div>
                <div class="col-7"><label class="form-label">Período</label><input name="period" class="form-control" value="{{ now()->translatedFormat('F Y') }}" required></div>
                <div class="col-5"><label class="form-label">Vencimiento</label><input type="date" name="due_date" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand"><i class="bi bi-calendar-plus me-1"></i> Generar</button></div>
    </form>
</div></div></div>
@endsection
