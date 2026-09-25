@extends('layouts.app')
@section('title', 'Inicio')

@section('content')
@php 
    $hour = (int) now()->format('H'); 
    $saludo = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches'); 
    $currency = $appSettings->currency ?? '$';
@endphp

{{-- Hero Compacto Verde 2026 --}}
<div class="hero-compact">
    <div class="hero-deco-1"></div>
    <div class="hero-deco-2"></div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1">
        <div>
            <div style="font-size:12.5px;opacity:.88;text-transform:capitalize;letter-spacing:0.02em">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </div>
            <h1 style="font-size:22px;font-weight:800;margin:4px 0 2px;letter-spacing:-0.02em">
                {{ $saludo }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
            </h1>
            <div style="opacity:.85;font-size:13px">
                {{ auth()->user()->school?->name ?? $appSettings->school_name ?? 'Colegio' }} · Ciclo Escolar {{ $appSettings->academic_year ?? date('Y') }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('students.create') }}" class="btn btn-sm" style="background:#ffffff;color:var(--brand-ink);font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                <i class="bi bi-person-plus-fill me-1 text-success"></i> Nuevo alumno
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(4px)">
                <i class="bi bi-bar-chart me-1"></i> Reportes
            </a>
        </div>
    </div>
</div>

{{-- Accesos Rápidos (Chips Modernos) --}}
<div class="d-flex align-items-center gap-2 flex-wrap mb-4">
    <span class="text-muted small fw-semibold text-uppercase me-1" style="font-size:11px;letter-spacing:0.04em">Accesos directos:</span>
    <a href="{{ route('attendances.index') }}" class="chip-btn">
        <i class="bi bi-calendar2-check"></i> Pase de lista
    </a>
    <a href="{{ route('payments.index') }}" class="chip-btn">
        <i class="bi bi-cash-stack"></i> Colegiaturas
    </a>
    <a href="{{ route('courses.index') }}" class="chip-btn">
        <i class="bi bi-mortarboard"></i> Grados y grupos
    </a>
    <a href="{{ route('grades.index') }}" class="chip-btn">
        <i class="bi bi-clipboard-data"></i> Calificaciones
    </a>
</div>

{{-- 4 KPIs Grandes Clicables (En Móvil 2 Columnas) --}}
<div class="kpi-row">
    {{-- KPI 1: Alumnos Activos --}}
    <a href="{{ route('students.index') }}" class="kpi-card">
        <div class="kpi-label">
            <span>Alumnos activos</span>
            <span class="kpi-icon" style="background:var(--brand-soft);color:var(--brand)">
                <i class="bi bi-people-fill"></i>
            </span>
        </div>
        <div class="kpi-value">{{ number_format($stats['students']) }}</div>
        <div class="kpi-foot">
            <span>Matrícula regular</span>
            <span class="text-success fw-semibold">Ver directorio <i class="bi bi-arrow-right"></i></span>
        </div>
    </a>

    {{-- KPI 2: Faltas de Hoy --}}
    <a href="{{ route('attendances.index', ['date' => today()->toDateString()]) }}" class="kpi-card">
        <div class="kpi-label">
            <span>Faltas de hoy</span>
            <span class="kpi-icon" style="background:#fee2e2;color:#ef4444">
                <i class="bi bi-person-x-fill"></i>
            </span>
        </div>
        <div class="kpi-value" style="color:{{ $stats['today_absences'] > 0 ? '#ef4444' : 'inherit' }}">
            {{ number_format($stats['today_absences']) }}
        </div>
        <div class="kpi-foot">
            <span>{{ $stats['today_absences'] === 0 ? 'Asistencia perfecta' : 'Inasistencias registradas' }}</span>
            <span class="text-muted fw-semibold">Pase de lista <i class="bi bi-arrow-right"></i></span>
        </div>
    </a>

    {{-- KPI 3: Por Cobrar MXN --}}
    <a href="{{ route('payments.index', ['status' => 'pendiente']) }}" class="kpi-card">
        <div class="kpi-label">
            <span>Por cobrar</span>
            <span class="kpi-icon" style="background:#fef3c7;color:#d97706">
                <i class="bi bi-clock-history"></i>
            </span>
        </div>
        <div class="kpi-value" style="color:#d97706">
            {{ $currency }} {{ number_format($income['pending'], 0) }}
        </div>
        <div class="kpi-foot">
            <span>Colegiaturas pendientes</span>
            <span class="text-warning-emphasis fw-semibold">Cobranza <i class="bi bi-arrow-right"></i></span>
        </div>
    </a>

    {{-- KPI 4: Cobrado del Mes MXN --}}
    <a href="{{ route('reports.index') }}" class="kpi-card">
        <div class="kpi-label">
            <span>Cobrado del mes</span>
            <span class="kpi-icon" style="background:#e0f2fe;color:#0284c7">
                <i class="bi bi-wallet2"></i>
            </span>
        </div>
        <div class="kpi-value" style="color:#0284c7">
            {{ $currency }} {{ number_format($income['month_paid'], 0) }}
        </div>
        <div class="kpi-foot">
            <span class="text-capitalize">{{ now()->translatedFormat('F Y') }}</span>
            <span class="text-primary fw-semibold">Finanzas <i class="bi bi-arrow-right"></i></span>
        </div>
    </a>
</div>

{{-- Sección "Hoy en el colegio" (2 columnas) --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <h2 style="font-size:15px;font-weight:700;margin:0;color:var(--ink)">
        <i class="bi bi-clock-history me-1 text-success"></i> Hoy en el colegio
    </h2>
    <span class="text-muted small" style="font-size:12px">Resumen operativo del día</span>
</div>
<div class="row g-3 mb-4">
    {{-- Columna Izquierda: Faltas de hoy --}}
    <div class="col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-calendar-x"></i> Faltas de hoy
                </span>
                <a href="{{ route('attendances.index', ['date' => today()->toDateString()]) }}" class="small text-muted fw-semibold">
                    Ver pase de lista
                </a>
            </div>
            <div class="card-body p-0">
                @if($todayAbsences->isEmpty())
                    <div class="empty-state py-4">
                        <div style="font-size:36px;margin-bottom:6px">🙌</div>
                        <strong class="d-block text-dark" style="font-size:14px">Sin faltas hoy</strong>
                        <span class="small text-muted">Todos los alumnos asistieron a clase puntualmente.</span>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Alumno</th>
                                    <th>Grado y grupo</th>
                                    <th class="text-end pe-3">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayAbsences as $abs)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar-round" style="width:30px;height:30px;font-size:11px;background:#fee2e2;color:#991b1b">
                                                    {{ mb_substr($abs->student?->first_name ?? 'A', 0, 1) }}{{ mb_substr($abs->student?->last_name ?? '', 0, 1) }}
                                                </span>
                                                <span class="fw-semibold text-dark">{{ $abs->student?->full_name ?? 'Alumno' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border">
                                                {{ $abs->course?->name ?? '—' }} {{ $abs->course?->section ? '"'.$abs->course->section.'"' : '' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge-soft badge-ausente">Inasistencia</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Colegiaturas vencidas --}}
    <div class="col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-receipt"></i> Colegiaturas vencidas
                </span>
                <a href="{{ route('payments.index', ['status' => 'vencido']) }}" class="small text-muted fw-semibold">
                    Ver cobranza
                </a>
            </div>
            <div class="card-body p-0">
                @if($overduePayments->isEmpty())
                    <div class="empty-state py-4">
                        <div style="font-size:36px;margin-bottom:6px">✨</div>
                        <strong class="d-block text-dark" style="font-size:14px">Al corriente</strong>
                        <span class="small text-muted">No hay colegiaturas con saldo vencido en el colegio.</span>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Alumno</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th class="text-end pe-3">Límite</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overduePayments as $op)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-dark" style="font-size:13px">
                                                {{ $op->student?->full_name ?? '—' }}
                                            </div>
                                            <div class="small text-muted" style="font-size:11px">
                                                {{ $op->student?->course?->name ?? 'Sin grupo' }}
                                            </div>
                                        </td>
                                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($op->concept, 22) }}</td>
                                        <td>
                                            <strong class="text-danger">{{ $currency }} {{ number_format($op->amount, 0) }}</strong>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge-soft badge-vencido">
                                                {{ $op->due_date ? $op->due_date->format('d/m/Y') : 'Vencido' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Gráfica Principal de Ingresos Mensuales + Comunicados --}}
<div class="grid-2 mb-4">
    <div class="card card-accent mb-0">
        <div class="card-header">
            <span class="title">
                <i class="bi bi-graph-up"></i> Recaudación de colegiaturas ({{ $currency }} MXN)
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle">Últimos 6 meses</span>
        </div>
        <div class="card-body">
            <div class="chart-box">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card mb-0">
        <div class="card-header">
            <span class="title">
                <i class="bi bi-megaphone"></i> Comunicados escolares
            </span>
            <a href="{{ route('announcements.index') }}" class="small text-muted fw-semibold">Ver todos</a>
        </div>
        <div class="card-body">
            @forelse($announcements as $a)
                <div class="mini-stat border-bottom">
                    <div class="ic" style="background:var(--brand-soft);color:var(--brand)">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <strong class="d-block text-dark text-truncate" style="font-size:13.5px">{{ $a->title }}</strong>
                        <span class="text-muted small">{{ \Illuminate\Support\Str::limit($a->body, 60) }} · {{ $a->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state py-4">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0">Sin comunicados recientes</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Accordion Colapsado para Indicadores Escolares (Género y Nivel) --}}
<div class="accordion accordion-modern mb-4" id="accordionMetrics">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingMetrics">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMetrics" aria-expanded="false" aria-controls="collapseMetrics">
                <i class="bi bi-bar-chart-steps me-2 text-success"></i>
                <span>Más indicadores escolares (Distribución por nivel y género)</span>
            </button>
        </h2>
        <div id="collapseMetrics" class="accordion-collapse collapse" aria-labelledby="headingMetrics" data-bs-parent="#accordionMetrics">
            <div class="accordion-body p-3">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="p-2">
                            <strong class="d-block text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:0.04em">Alumnos por nivel educativo</strong>
                            <div style="height:220px;position:relative">
                                <canvas id="levelChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="p-2 border-start-md">
                            <strong class="d-block text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:0.04em">Distribución por género</strong>
                            <div style="height:220px;position:relative">
                                <canvas id="genderChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabla de Alumnos Recientes (2026 Clean Design) --}}
<div class="card mb-4">
    <div class="card-header">
        <span class="title">
            <i class="bi bi-people"></i> Últimos alumnos inscritos
        </span>
        <a href="{{ route('students.index') }}" class="small text-muted fw-semibold">Ver directorio completo</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Matrícula</th>
                        <th>Alumno</th>
                        <th>Grado y grupo</th>
                        <th>Padre o tutor</th>
                        <th class="text-end pe-3">Estatus</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentStudents as $s)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-secondary border font-monospace" style="font-size:11px">
                                {{ $s->code }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-round">
                                    {{ mb_substr($s->first_name, 0, 1) }}{{ mb_substr($s->last_name, 0, 1) }}
                                </span>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $s->full_name }}</div>
                                    @if($s->curp)<span class="text-muted" style="font-size:11px">{{ $s->curp }}</span>@endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-dark">{{ optional($s->course)->name ?? '—' }}</span>
                            @if(optional($s->course)->section)
                                <span class="text-muted small">"{{ $s->course->section }}"</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $s->guardian_name ?? '—' }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <span class="badge-soft badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state py-4">Aún no hay alumnos registrados en el ciclo escolar</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const incomeData = @json($monthlyIncome);
const levelData = @json($studentsByLevel);
const genderData = @json($genderDistribution);

// Gráfica de ingresos: área con degradado verde suave, números en pesos, sin leyenda
const incomeCtx = document.getElementById('incomeChart');
if (incomeCtx) {
    const ctx = incomeCtx.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(22, 163, 74, 0.28)');
    gradient.addColorStop(1, 'rgba(22, 163, 74, 0.01)');

    new Chart(incomeCtx, {
        type: 'line',
        data: {
            labels: Object.keys(incomeData),
            datasets: [{
                label: 'Ingresos cobrados',
                data: Object.values(incomeData),
                borderColor: '#16a34a',
                borderWidth: 2.5,
                backgroundColor: gradient,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#16a34a',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Cobrado: $' + Number(context.parsed.y).toLocaleString('es-MX', { minimumFractionDigits: 0 }) + ' MXN';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: {
                        callback: function(value) {
                            return '$' + Number(value).toLocaleString('es-MX');
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Gráfica de distribución por género
const genderCtx = document.getElementById('genderChart');
if (genderCtx) {
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hombres', 'Mujeres'],
            datasets: [{
                data: [genderData.M || 0, genderData.F || 0],
                backgroundColor: ['#0ea5a3', '#16a34a'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } }
            }
        }
    });
}

// Gráfica de distribución por nivel
const levelCtx = document.getElementById('levelChart');
if (levelCtx) {
    new Chart(levelCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(levelData),
            datasets: [{
                label: 'Alumnos',
                data: Object.values(levelData),
                backgroundColor: '#16a34a',
                borderRadius: 8,
                maxBarThickness: 45
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(226, 232, 240, 0.6)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
