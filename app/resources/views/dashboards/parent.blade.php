@extends('layouts.app')
@section('title', 'Portal Familiar')

@section('content')
@php
    $currency = $appSettings->currency ?? '$';
    $totalAsist = array_sum($attendanceSummary);
    $pctAsist = $totalAsist ? round(($attendanceSummary['presente'] / $totalAsist) * 100) : 100;
    
    // Próxima colegiatura a vencer
    $nextPayment = $payments->whereIn('status', ['pendiente', 'vencido'])->sortBy('due_date')->first();
    $totalPendiente = $payments->whereIn('status', ['pendiente', 'vencido'])->sum('amount');

    // Asistencia de la semana actual (L M M J V)
    $startOfWeek = now()->startOfWeek();
    $weeklyAttendances = \App\Models\Attendance::where('student_id', $student->id)
        ->whereBetween('date', [$startOfWeek->toDateString(), now()->endOfWeek()->toDateString()])
        ->get()
        ->keyBy(fn($a) => $a->date->format('Y-m-d'));

    $weekDays = [
        ['label' => 'L', 'name' => 'Lunes', 'date' => $startOfWeek->copy()->addDays(0)],
        ['label' => 'M', 'name' => 'Martes', 'date' => $startOfWeek->copy()->addDays(1)],
        ['label' => 'M', 'name' => 'Miércoles', 'date' => $startOfWeek->copy()->addDays(2)],
        ['label' => 'J', 'name' => 'Jueves', 'date' => $startOfWeek->copy()->addDays(3)],
        ['label' => 'V', 'name' => 'Viernes', 'date' => $startOfWeek->copy()->addDays(4)],
    ];
@endphp

{{-- Cabecera / Saludo --}}
<div class="page-head mb-3">
    <div>
        <h1 style="font-size:22px;font-weight:800;letter-spacing:-0.02em">Hola, {{ auth()->user()->name }} 👋</h1>
        <div class="breadcrumb-mini">Portal Familiar · Seguimiento Escolar y Colegiaturas</div>
    </div>
    @if($student)
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('students.boletin', $student) }}" target="_blank" class="chip-btn">
                <i class="bi bi-file-earmark-pdf text-danger"></i> Boleta oficial
            </a>
            <a href="{{ route('students.estadoCuenta', $student) }}" target="_blank" class="chip-btn">
                <i class="bi bi-receipt text-primary"></i> Estado de cuenta
            </a>
            <a href="{{ route('students.carnet', $student) }}" target="_blank" class="chip-btn">
                <i class="bi bi-person-vcard text-success"></i> Credencial
            </a>
        </div>
    @endif
</div>

{{-- Selector de Hijos (Tabs/Chips Limpias) --}}
@if($children->count() > 1)
    <div class="d-flex align-items-center gap-2 flex-wrap mb-4">
        <span class="text-muted small fw-semibold text-uppercase me-1" style="font-size:11px;letter-spacing:0.04em">Hijos:</span>
        @foreach($children as $ch)
            <a href="?student={{ $ch->id }}" class="child-tab {{ $student && $student->id === $ch->id ? 'active' : '' }}">
                <span class="avatar-dot">{{ mb_substr($ch->first_name, 0, 1) }}</span>
                <span>{{ $ch->full_name }}</span>
                <span class="badge {{ $student && $student->id === $ch->id ? 'bg-light text-dark' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size:10.5px">
                    {{ optional($ch->course)->name ?? 'Sin grupo' }}
                </span>
            </a>
        @endforeach
    </div>
@elseif($student)
    <div class="d-flex align-items-center gap-2 mb-3 text-muted small">
        <i class="bi bi-mortarboard-fill text-success"></i> Alumno consultado: <strong class="text-dark">{{ $student->full_name }}</strong> · Grado y grupo: <strong class="text-dark">{{ optional($student->course)->name ?? 'Sin asignar' }} {{ optional($student->course)->section ? '"'.$student->course->section.'"' : '' }}</strong>
    </div>
@endif

{{-- Hero de Resumen: Próxima Colegiatura + Promedio + Asistencia Semanal --}}
<div class="card mb-4 border-0 shadow-sm" style="background:#ffffff;border-radius:18px;overflow:hidden">
    <div class="row g-0">
        {{-- Próxima Colegiatura a Vencer --}}
        <div class="col-lg-6 p-4 border-end-lg" style="background:linear-gradient(135deg, #0B1A14 0%, #15803d 100%);color:#ffffff">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-white text-dark rounded-pill px-3 py-1" style="font-size:11px;font-weight:700">
                    <i class="bi bi-credit-card-2-front me-1 text-success"></i> Colegiatura
                </span>
                @if($nextPayment && $nextPayment->due_date)
                    <span style="font-size:12px;opacity:.9">
                        Vence: <strong>{{ $nextPayment->due_date->format('d/m/Y') }}</strong>
                    </span>
                @endif
            </div>

            @if($nextPayment)
                <div class="my-2">
                    <div style="font-size:12.5px;opacity:.85;text-transform:uppercase;letter-spacing:0.04em">Próxima a pagar</div>
                    <div style="font-size:32px;font-weight:800;letter-spacing:-0.03em;line-height:1.2;margin:4px 0">
                        {{ $currency }} {{ number_format($nextPayment->amount, 2) }}
                    </div>
                    <div style="font-size:13.5px;opacity:.9;margin-bottom:16px">
                        {{ $nextPayment->concept }} @if($nextPayment->period) · Periodo {{ $nextPayment->period }} @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('parent.payments.checkout', $nextPayment) }}" class="btn btn-brand btn-pill px-4 py-2" style="background:#22c55e;color:#0B1A14;font-weight:700;border:none">
                        <i class="bi bi-wallet2 me-1"></i> Pagar colegiatura en línea
                    </a>
                    <a href="{{ route('students.estadoCuenta', $student) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        Estado de cuenta
                    </a>
                </div>
            @else
                <div class="py-3">
                    <div style="font-size:28px;margin-bottom:6px">🙌</div>
                    <h3 style="font-size:20px;font-weight:700;margin:0 0 4px">¡Al corriente en colegiaturas!</h3>
                    <p style="font-size:13px;opacity:.85;margin-bottom:14px">No tienes saldos pendientes por liquidar para {{ $student->first_name }}.</p>
                    <a href="{{ route('students.estadoCuenta', $student) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        Ver historial de pagos
                    </a>
                </div>
            @endif
        </div>

        {{-- Resumen Académico: Promedio General y Asistencia de la Semana --}}
        <div class="col-lg-6 p-4 d-flex flex-column justify-content-between" style="background:#ffffff">
            <div class="row g-3 align-items-center">
                {{-- Promedio General --}}
                <div class="col-sm-5">
                    <div class="p-3 rounded-4" style="background:#f8fafc;border:1px solid var(--line-light)">
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size:11px;letter-spacing:0.04em">Promedio general</div>
                        <div class="d-flex align-items-baseline gap-1 my-1">
                            <span style="font-size:32px;font-weight:800;color:var(--brand);letter-spacing:-0.03em">
                                {{ $average !== null ? number_format($average, 1) : '—' }}
                            </span>
                            <span class="text-muted small fw-semibold">/ 10</span>
                        </div>
                        <div class="small text-muted" style="font-size:11.5px">
                            {{ $grades->count() }} notas registradas
                        </div>
                    </div>
                </div>

                {{-- Asistencia de la semana en bolitas L M M J V --}}
                <div class="col-sm-7">
                    <div class="p-3 rounded-4" style="background:#f8fafc;border:1px solid var(--line-light)">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size:11px;letter-spacing:0.04em">Esta semana</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10.5px">{{ $pctAsist }}% global</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-1">
                            @foreach($weekDays as $wd)
                                @php
                                    $dStr = $wd['date']->toDateString();
                                    $att = $weeklyAttendances->get($dStr);
                                    $isFuture = $wd['date']->isFuture() && !$wd['date']->isToday();
                                    $dotClass = 'vacio';
                                    $dotTitle = $wd['name'] . ': Sin registro';

                                    if ($att) {
                                        $dotClass = $att->status; // presente, tardanza, ausente, justificado
                                        $dotTitle = $wd['name'] . ': ' . ucfirst($att->status);
                                    } elseif ($isFuture) {
                                        $dotTitle = $wd['name'] . ': Por ocurrir';
                                    } elseif ($wd['date']->isToday()) {
                                        $dotTitle = $wd['name'] . ' (Hoy)';
                                    }
                                @endphp
                                <div class="text-center">
                                    <div class="day-dot {{ $dotClass }}" title="{{ $dotTitle }}" data-bs-toggle="tooltip">
                                        {{ $wd['label'] }}
                                    </div>
                                    <span class="text-muted d-block mt-1" style="font-size:10px">{{ $wd['date']->format('d') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top small text-muted">
                <span>Materias cursando: <strong class="text-dark">{{ $bySubject->count() }}</strong></span>
                <a href="{{ route('students.boletin', $student) }}" target="_blank" class="text-success fw-semibold">
                    Ver boleta oficial <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Tareas Pendientes y Calificaciones --}}
<div class="row g-3 mb-4">
    {{-- Tareas Escolares --}}
    <div class="col-lg-7">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-journal-text"></i> Tareas y actividades escolares
                </span>
                <span class="badge bg-light text-secondary border">{{ $assignments->count() }} activas</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Tarea</th>
                                <th>Materia</th>
                                <th>Fecha límite</th>
                                <th class="text-end pe-3">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $a)
                            @php $sub = $submissionsMap->get($a->id); @endphp
                            <tr>
                                <td class="ps-3">
                                    <strong class="d-block text-dark" style="font-size:13px">{{ $a->title }}</strong>
                                    <span class="text-muted small">{{ \Illuminate\Support\Str::limit($a->description, 45) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ optional($a->subject)->name }}</span>
                                </td>
                                <td>
                                    <span class="small {{ $a->is_overdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                                        {{ $a->due_date->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($sub && $sub->status === 'revisado')
                                        <span class="badge-soft badge-activo">Calificada: {{ $sub->score ?? '—' }}</span>
                                    @elseif($sub)
                                        <span class="badge-soft badge-pendiente">Entregada</span>
                                    @else
                                        <span class="badge bg-light text-secondary border">Sin entregar</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state py-4">No hay tareas escolares pendientes 🎉</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Calificaciones por Materia --}}
    <div class="col-lg-5">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-clipboard-data"></i> Calificaciones recientes
                </span>
                <a href="{{ route('students.boletin', $student) }}" target="_blank" class="small text-muted fw-semibold">Boleta completa</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Materia</th>
                                <th>Periodo</th>
                                <th class="text-end pe-3">Calificación</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($grades->take(6) as $g)
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark" style="font-size:13px">{{ optional($g->subject)->name }}</strong>
                                </td>
                                <td><span class="text-muted small">{{ $g->period }}</span></td>
                                <td class="text-end pe-3">
                                    <span class="badge-soft {{ $g->score >= 6.0 ? 'badge-activo' : 'badge-vencido' }} fw-bold" style="font-size:12px">
                                        {{ number_format($g->score, 1) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state py-4">Sin calificaciones registradas aún</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Estado de Cuenta y Colegiaturas --}}
<div class="card mb-4">
    <div class="card-header">
        <span class="title">
            <i class="bi bi-cash-stack"></i> Historial de pagos y colegiaturas
        </span>
        <a href="{{ route('students.estadoCuenta', $student) }}" target="_blank" class="small text-muted fw-semibold">
            Descargar estado de cuenta
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Concepto</th>
                        <th>Periodo</th>
                        <th>Monto</th>
                        <th>Fecha límite / Pago</th>
                        <th>Estatus</th>
                        <th class="text-end pe-3">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments->take(8) as $p)
                    @php $comp = $p->comprobante(); @endphp
                    <tr>
                        <td class="ps-3">
                            <strong class="text-dark" style="font-size:13.5px">{{ $p->concept }}</strong>
                        </td>
                        <td><span class="text-muted small">{{ $p->period ?? '—' }}</span></td>
                        <td>
                            <strong>{{ $currency }} {{ number_format($p->amount, 2) }}</strong>
                        </td>
                        <td>
                            <span class="text-muted small">
                                {{ $p->status === 'pagado' ? optional($p->paid_date)->format('d/m/Y') : optional($p->due_date)->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-soft badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td class="text-end pe-3">
                            @if($comp && $comp->estado === 'aceptado')
                                <a href="{{ route('facturacion.pdf', $comp) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Descargar PDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            @elseif($p->status === 'pagado')
                                <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-light border" title="Recibo">
                                    <i class="bi bi-file-earmark-pdf text-danger me-1"></i> Recibo
                                </a>
                            @else
                                <a href="{{ route('parent.payments.checkout', $p) }}" class="btn btn-sm btn-brand btn-pill text-nowrap">
                                    Pagar colegiatura
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state py-4">Sin registros de colegiaturas</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(optional($appSettings)->spei_enabled && $appSettings->spei_clabe)
        <div class="card-footer bg-light py-2 px-3 small text-muted">
            <i class="bi bi-bank text-success me-1"></i> <strong>Transferencia SPEI:</strong> Banco {{ $appSettings->spei_bank }} · CLABE: <code>{{ $appSettings->spei_clabe }}</code> · Beneficiario: {{ $appSettings->spei_beneficiary }}
        </div>
    @endif
</div>

{{-- Comunicados --}}
<div class="card mb-4">
    <div class="card-header">
        <span class="title"><i class="bi bi-megaphone"></i> Comunicados escolares</span>
    </div>
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
                <div class="empty-state py-4 text-muted">No hay comunicados escolares recientes.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
