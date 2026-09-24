@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <div><h1>Hola, {{ auth()->user()->name }}</h1><div class="breadcrumb-mini">Panel del estudiante · {{ optional(optional($student)->course)->name }}</div></div>
    @if($student)
        <div class="d-flex gap-2">
            <a href="{{ route('students.boletin', $student) }}" class="btn btn-danger btn-icon"><i class="bi bi-file-earmark-pdf"></i> Mi boleta</a>
            <a href="{{ route('students.estadoCuenta', $student) }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-receipt"></i> Estado de cuenta</a>
        </div>
    @endif
</div>

@unless($student)
    <div class="alert alert-warning"><i class="bi bi-info-circle me-2"></i>Tu usuario no está vinculado a un registro de estudiante. Pide al administrador que lo asocie.</div>
@endunless

@php
    $totalAsist = array_sum($attendanceSummary);
    $pctAsist = $totalAsist ? round(($attendanceSummary['presente'] / $totalAsist) * 100) : 0;
    $pendiente = $payments->whereIn('status', ['pendiente','vencido'])->sum('amount');
@endphp

<div class="stats-row">
    <div class="stat-card bg-teal"><div class="label">Promedio general</div><div class="value">{{ $average ?? '—' }}</div><i class="bi bi-clipboard-data icon"></i></div>
    <div class="stat-card bg-green"><div class="label">Asistencia</div><div class="value">{{ $pctAsist }}%</div><i class="bi bi-calendar2-check icon"></i></div>
    <div class="stat-card bg-blue"><div class="label">Materias</div><div class="value">{{ $bySubject->count() }}</div><i class="bi bi-journal-bookmark icon"></i></div>
    <div class="stat-card {{ $pendiente>0 ? 'bg-red' : 'bg-dark' }}"><div class="label">Saldo pendiente</div><div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($pendiente,0) }}</div><i class="bi bi-cash icon"></i></div>
</div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-bar-chart"></i> Mi rendimiento por materia</span></div>
        <div class="card-body"><div class="chart-box"><canvas id="subjChart"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-calendar2-check"></i> Resumen de asistencia</span></div>
        <div class="card-body">
            @foreach(['presente'=>'success','tardanza'=>'warning','justificado'=>'info','ausente'=>'danger'] as $st=>$col)
                <div class="d-flex justify-content-between mb-1"><span class="text-capitalize">{{ $st }}</span><span>{{ $attendanceSummary[$st] ?? 0 }}</span></div>
                <div class="progress mb-3"><div class="progress-bar bg-{{ $col }}" style="width:{{ $totalAsist ? ($attendanceSummary[$st]/$totalAsist*100) : 0 }}%"></div></div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="title"><i class="bi bi-journal-text"></i> Tareas pendientes</span></div>
    <div class="card-body p-0"><table class="table mb-0"><thead><tr><th class="ps-3">Tarea</th><th>Materia</th><th>Entrega</th><th class="text-end pe-3">Estado / Acción</th></tr></thead><tbody>
    @forelse($assignments as $a)
        @php $sub = $submissionsMap->get($a->id); @endphp
        <tr>
            <td class="ps-3"><strong>{{ $a->title }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($a->description, 60) }}</div></td>
            <td>{{ optional($a->subject)->name }}</td>
            <td>{{ $a->due_date->format('d/m/Y') }} @if($a->is_overdue)<span class="badge-soft badge-vencido ms-1">Vencida</span>@elseif($a->due_date->isToday())<span class="badge-soft badge-pendiente ms-1">¡Hoy!</span>@endif</td>
            <td class="text-end pe-3">
                @if($sub && $sub->status==='revisado')
                    <span class="badge-soft badge-activo">Revisada{{ $sub->score!==null ? ': '.$sub->score : '' }}</span>
                @elseif($sub)
                    <span class="badge-soft badge-pendiente">Entregada</span>
                    <button class="btn btn-sm btn-light" onclick="prepSubmit('{{ $a->id }}','{{ addslashes($a->title) }}')" data-bs-toggle="modal" data-bs-target="#mSubmit">Reenviar</button>
                @else
                    <button class="btn btn-sm btn-brand" onclick="prepSubmit('{{ $a->id }}','{{ addslashes($a->title) }}')" data-bs-toggle="modal" data-bs-target="#mSubmit"><i class="bi bi-upload"></i> Entregar</button>
                @endif
            </td>
        </tr>
    @empty<tr><td colspan="4" class="empty-state">No tienes tareas pendientes 🎉</td></tr>@endforelse
    </tbody></table></div>
</div>

<div class="modal fade" id="mSubmit"><div class="modal-dialog"><div class="modal-content">
    <form id="submitForm" method="POST" enctype="multipart/form-data">@csrf
        <div class="modal-header"><h5 class="modal-title">Entregar tarea</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="text-muted small mb-2" id="submitTitle"></p>
            <div class="mb-2"><label class="form-label">Comentario</label><textarea name="comment" class="form-control" rows="3" placeholder="Opcional"></textarea></div>
            <div class="mb-2"><label class="form-label">Archivo (opcional)</label><input type="file" name="file" class="form-control"><div class="form-text">Máx 5MB.</div></div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand"><i class="bi bi-send"></i> Enviar entrega</button></div>
    </form>
</div></div></div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-clipboard-data"></i> Mis calificaciones</span></div>
        <div class="card-body p-0"><table class="table mb-0"><thead><tr><th class="ps-3">Materia</th><th>Periodo</th><th>Tipo</th><th>Nota</th></tr></thead><tbody>
        @forelse($grades->take(12) as $g)
            <tr><td class="ps-3">{{ optional($g->subject)->name }}</td><td class="text-muted">{{ $g->period }}</td><td>{{ ucfirst($g->type) }}</td>
                <td><span class="badge-soft {{ $g->score>=6.0 ? 'badge-activo':'badge-vencido' }}">{{ $g->score }}</span></td></tr>
        @empty<tr><td colspan="4" class="empty-state">Sin calificaciones</td></tr>@endforelse
        </tbody></table></div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="title"><i class="bi bi-cash-stack"></i> Mis Pagos y Facturas CFDI</span>
            <a href="{{ route('students.estadoCuenta', $student) }}" class="btn btn-sm btn-light"><i class="bi bi-receipt"></i> Estado de cuenta</a>
        </div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th class="ps-3">Concepto</th><th>Monto</th><th>Estado</th><th class="text-end pe-3">Factura SAT</th></tr></thead><tbody>
        @forelse($payments->take(10) as $p)
            @php $comp = $p->comprobante(); @endphp
            <tr>
                <td class="ps-3"><strong>{{ $p->concept }}</strong>@if($p->period)<div class="small text-muted">{{ $p->period }}</div>@endif</td>
                <td><strong>{{ $appSettings->currency ?? '$' }} {{ number_format($p->amount,2) }}</strong></td>
                <td><span class="badge-soft badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                <td class="text-end pe-3">
                    @if($comp && $comp->estado === 'aceptado')
                        <a href="{{ route('facturacion.pdf', $comp) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Descargar PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                        <a href="{{ route('facturacion.xml', $comp) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar XML"><i class="bi bi-filetype-xml"></i> XML</a>
                    @elseif($p->status === 'pagado')
                        <span class="badge bg-light text-muted border">En proceso fiscal</span>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
            </tr>
        @empty<tr><td colspan="4" class="empty-state">Sin pagos registrados</td></tr>@endforelse
        </tbody></table></div></div>
        @if(optional($appSettings)->spei_enabled && $appSettings->spei_clabe)
            <div class="card-footer bg-light py-2 px-3 small">
                <i class="bi bi-bank text-primary me-1"></i> <strong>Pago por Transferencia SPEI:</strong> Banco {{ $appSettings->spei_bank }} · CLABE: <code>{{ $appSettings->spei_clabe }}</code> · Beneficiario: {{ $appSettings->spei_beneficiary }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const submitBase = "{{ url('tareas') }}";
function prepSubmit(id, title){
    document.getElementById('submitForm').action = submitBase + '/' + id + '/entregar';
    document.getElementById('submitTitle').textContent = title;
}
new Chart(document.getElementById('subjChart'),{type:'bar',
    data:{labels:@json($bySubject->pluck('subject')),datasets:[{label:'Promedio',data:@json($bySubject->pluck('avg')),backgroundColor:'#1abc9c',borderRadius:6,maxBarThickness:46}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:10}}}});
</script>
@endpush
