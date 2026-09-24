@extends('layouts.app')
@section('title', 'Portal de Padres / Tutores')

@section('content')
<div class="page-head">
    <div>
        <h1>Hola, {{ auth()->user()->name }}</h1>
        <div class="breadcrumb-mini">Portal Familiar · Expediente Escolar</div>
    </div>
    @if($student)
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('students.boletin', $student) }}" target="_blank" class="btn btn-danger btn-icon">
                <i class="bi bi-file-earmark-pdf"></i> Boleta Oficial
            </a>
            <a href="{{ route('students.estadoCuenta', $student) }}" target="_blank" class="btn btn-outline-secondary btn-icon">
                <i class="bi bi-receipt"></i> Estado de Cuenta
            </a>
            <a href="{{ route('students.constancia', $student) }}" target="_blank" class="btn btn-light btn-icon">
                <i class="bi bi-file-text"></i> Constancia
            </a>
            <a href="{{ route('students.carnet', $student) }}" target="_blank" class="btn btn-light btn-icon">
                <i class="bi bi-person-vcard"></i> Credencial
            </a>
        </div>
    @endif
</div>

<!-- SELECTOR DE HIJOS SI TIENE MÁS DE 1 -->
@if($children->count() > 1)
    <div class="card mb-4 bg-light border">
        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary fs-5"></i>
                <span class="fw-bold">Viendo a:</span>
                <span class="badge bg-primary fs-6">{{ $student->full_name }}</span>
                <span class="text-muted small">({{ optional($student->course)->name ?? 'Sin curso' }})</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 small text-muted">Cambiar de alumno:</label>
                <select class="form-select form-select-sm" style="width: auto; min-width: 220px;" onchange="window.location.href='?student=' + this.value">
                    @foreach($children as $ch)
                        <option value="{{ $ch->id }}" @selected($student && $student->id === $ch->id)>
                            {{ $ch->full_name }} — {{ optional($ch->course)->name ?? 'Sin grupo' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@elseif($student)
    <div class="d-flex align-items-center gap-2 mb-3 text-muted small">
        <i class="bi bi-person-check-fill text-success"></i> Estudiante vinculado: <strong>{{ $student->full_name }}</strong> ({{ optional($student->course)->name ?? 'Sin curso' }})
    </div>
@endif

@php
    $totalAsist = array_sum($attendanceSummary);
    $pctAsist = $totalAsist ? round(($attendanceSummary['presente'] / $totalAsist) * 100) : 0;
    $pendiente = $payments->whereIn('status', ['pendiente','vencido'])->sum('amount');
@endphp

<div class="stats-row">
    <div class="stat-card bg-teal">
        <div class="label">Promedio de {{ $student->first_name }}</div>
        <div class="value">{{ $average ?? '—' }}</div>
        <i class="bi bi-clipboard-data icon"></i>
    </div>
    <div class="stat-card bg-green">
        <div class="label">Asistencia escolar</div>
        <div class="value">{{ $pctAsist }}%</div>
        <i class="bi bi-calendar2-check icon"></i>
    </div>
    <div class="stat-card bg-blue">
        <div class="label">Materias inscritas</div>
        <div class="value">{{ $bySubject->count() }}</div>
        <i class="bi bi-journal-bookmark icon"></i>
    </div>
    <div class="stat-card {{ $pendiente > 0 ? 'bg-red' : 'bg-dark' }}">
        <div class="label">Colegiaturas / Saldo</div>
        <div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($pendiente, 2) }}</div>
        <i class="bi bi-cash icon"></i>
    </div>
</div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-bar-chart"></i> Rendimiento por materia</span></div>
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

<div class="card mb-4">
    <div class="card-header"><span class="title"><i class="bi bi-journal-text"></i> Tareas y actividades escolares</span></div>
    <div class="card-body p-0"><table class="table mb-0"><thead><tr><th class="ps-3">Tarea</th><th>Materia</th><th>Fecha Límite</th><th class="text-end pe-3">Estado de entrega</th></tr></thead><tbody>
    @forelse($assignments as $a)
        @php $sub = $submissionsMap->get($a->id); @endphp
        <tr>
            <td class="ps-3"><strong>{{ $a->title }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($a->description, 60) }}</div></td>
            <td>{{ optional($a->subject)->name }}</td>
            <td>{{ $a->due_date->format('d/m/Y') }} @if($a->is_overdue)<span class="badge-soft badge-vencido ms-1">Vencida</span>@elseif($a->due_date->isToday())<span class="badge-soft badge-pendiente ms-1">¡Hoy!</span>@endif</td>
            <td class="text-end pe-3">
                @if($sub && $sub->status==='revisado')
                    <span class="badge-soft badge-activo">Calificada{{ $sub->score!==null ? ': '.$sub->score : '' }}</span>
                @elseif($sub)
                    <span class="badge-soft badge-pendiente">Entregada</span>
                @else
                    <span class="badge bg-light text-secondary border">Sin entregar</span>
                @endif
            </td>
        </tr>
    @empty<tr><td colspan="4" class="empty-state">No hay tareas pendientes registradas 🎉</td></tr>@endforelse
    </tbody></table></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-clipboard-data"></i> Calificaciones del alumno</span></div>
        <div class="card-body p-0"><table class="table mb-0"><thead><tr><th class="ps-3">Materia</th><th>Periodo</th><th>Evaluación</th><th>Nota</th></tr></thead><tbody>
        @forelse($grades->take(12) as $g)
            <tr>
                <td class="ps-3">{{ optional($g->subject)->name }}</td>
                <td class="text-muted">{{ $g->period }}</td>
                <td>{{ ucfirst($g->type) }}</td>
                <td><span class="badge-soft {{ $g->score>=6.0 ? 'badge-activo':'badge-vencido' }}">{{ $g->score }}</span></td>
            </tr>
        @empty<tr><td colspan="4" class="empty-state">Sin calificaciones registradas</td></tr>@endforelse
        </tbody></table></div>
    </div>
    
    <!-- ESTADO DE CUENTA Y COLEGIATURAS -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="title"><i class="bi bi-cash-stack"></i> Pagos y Colegiaturas</span>
            <a href="{{ route('students.estadoCuenta', $student) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-receipt"></i> Estado de cuenta</a>
        </div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th class="ps-3">Concepto</th><th>Monto</th><th>Estado</th><th class="text-end pe-3">Factura</th></tr></thead><tbody>
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
                        <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap" title="Descargar Recibo en PDF">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Recibo
                        </a>
                    @else
                        <a href="{{ route('parent.payments.checkout', $p) }}" class="btn btn-sm btn-success fw-bold text-nowrap shadow-sm">
                            <i class="bi bi-credit-card-2-front me-1"></i> Pagar Colegiatura
                        </a>
                    @endif
                </td>
            </tr>
        @empty<tr><td colspan="4" class="empty-state">Sin registros de pago</td></tr>@endforelse
        </tbody></table></div></div>
        @if(optional($appSettings)->spei_enabled && $appSettings->spei_clabe)
            <div class="card-footer bg-light py-2 px-3 small">
                <i class="bi bi-bank text-primary me-1"></i> <strong>Pago por Transferencia SPEI:</strong> Banco {{ $appSettings->spei_bank }} · CLABE: <code>{{ $appSettings->spei_clabe }}</code> · Beneficiario: {{ $appSettings->spei_beneficiary }}
            </div>
        @endif
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><span class="title"><i class="bi bi-megaphone"></i> Comunicados del Colegio</span></div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($announcements as $an)
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">{{ $an->title }}</strong>
                        <small class="text-muted">{{ optional($an->published_at)->format('d/m/Y') }}</small>
                    </div>
                    <p class="small text-muted mb-0">{{ \Illuminate\Support\Str::limit($an->body, 140) }}</p>
                </div>
            @empty
                <div class="empty-state p-4 text-center text-muted">No hay comunicados recientes dirigidos a padres de familia.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('subjChart'),{type:'bar',
    data:{labels:@json($bySubject->pluck('subject')),datasets:[{label:'Promedio',data:@json($bySubject->pluck('avg')),backgroundColor:'#1abc9c',borderRadius:6,maxBarThickness:46}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:10}}}});
</script>
@endpush
