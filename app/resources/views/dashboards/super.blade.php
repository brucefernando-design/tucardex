@extends('layouts.app')
@section('title', 'Plataforma SaaS · Panel de Control Global')

@section('content')
<div style="position:relative;overflow:hidden;border-radius:var(--radius);padding:28px 32px;margin-bottom:24px;
            background:linear-gradient(135deg,#064e3b,#047857 50%,#0f172a 120%);color:#fff;box-shadow:var(--shadow)">
    <div style="position:absolute;right:-40px;top:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.08);filter:blur(30px)"></div>
    <div style="position:relative;z-index:1" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div style="font-size:13px;opacity:.85;text-transform:uppercase;letter-spacing:1px;font-weight:700">Centro de Operaciones Multi-Colegio</div>
            <h1 style="font-size:26px;font-weight:800;margin:6px 0 4px">Bienvenido, {{ explode(' ', auth()->user()->name)[0] }} 🚀</h1>
            <div style="opacity:.9;font-size:14px">Monitoreo global de instituciones, suscripciones recurrentes e ingresos.</div>
        </div>
        <div>
            <a href="{{ route('schools.index') }}" class="btn btn-light btn-icon fw-bold shadow-sm">
                <i class="bi bi-buildings"></i> Ver Todos los Colegios
            </a>
        </div>
    </div>
</div>

{{-- Fila de Métricas Clave SaaS --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:14px; background:#f0fdf4; border-left:4px solid #16a34a !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Colegios Activos</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['active'] }} <span class="fs-6 text-muted fw-normal">/ {{ $stats['schools'] }}</span></h3>
                    <small class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>{{ $stats['schools'] ? round($stats['active']/$stats['schools']*100) : 0 }}% en servicio</small>
                </div>
                <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:22px;">
                    <i class="bi bi-buildings"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:14px; background:#eff6ff; border-left:4px solid #2563eb !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Alumnos en Red</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">{{ number_format($stats['students']) }}</h3>
                    <small class="text-primary fw-bold"><i class="bi bi-people me-1"></i>Matrícula global activa</small>
                </div>
                <div style="width:48px;height:48px;border-radius:12px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:22px;">
                    <i class="bi bi-mortarboard"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:14px; background:#fefce8; border-left:4px solid #ca8a04 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">MRR Suscripciones</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">${{ number_format($stats['mrr'], 2) }}</h3>
                    <small class="text-warning-emphasis fw-bold"><i class="bi bi-arrow-repeat me-1"></i>Facturación mensual est.</small>
                </div>
                <div style="width:48px;height:48px;border-radius:12px;background:#fef9c3;color:#ca8a04;display:flex;align-items:center;justify-content:center;font-size:22px;">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:14px; background:#faf5ff; border-left:4px solid #9333ea !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Cobranza del Mes</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">${{ number_format($stats['tuition_volume'], 2) }}</h3>
                    <small class="text-purple fw-bold" style="color:#9333ea"><i class="bi bi-wallet2 me-1"></i>Procesado en colegiaturas</small>
                </div>
                <div style="width:48px;height:48px;border-radius:12px;background:#f3e8ff;color:#9333ea;display:flex;align-items:center;justify-content:center;font-size:22px;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Tabla de Colegios con Botón Directo de Suplantación --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                <span class="title fw-bold"><i class="bi bi-grid-fill text-success me-2"></i> Directorio de Instituciones</span>
                <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary btn-sm">Ver todos ({{ $stats['schools'] }})</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Colegio</th>
                                <th>Plan</th>
                                <th>Alumnos</th>
                                <th>Cuota Mensual</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acceso Rápido</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recentSchools as $s)
                            @php $sub = $s->calculateMonthlySubscription(); @endphp
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('schools.show', $s) }}" class="text-decoration-none fw-bold text-dark d-block">
                                        {{ $s->name }}
                                    </a>
                                    <span class="small text-muted font-monospace">{{ $s->slug }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize">{{ $s->plan }}</span>
                                </td>
                                <td>
                                    <strong>{{ $s->students_count }}</strong>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">${{ number_format($sub['total'], 2) }}</span>
                                    <span class="small text-muted">MXN</span>
                                </td>
                                <td>
                                    @if($s->status === 'activo')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Suspendido</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-1">
                                        <form action="{{ route('schools.impersonate', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Deseas iniciar sesión como Director de {{ $s->name }} en modo soporte? Podrás regresar en cualquier momento con la barra superior.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Entrar al panel de este colegio sin contraseña">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                                            </button>
                                        </form>
                                        <a href="{{ route('schools.show', $s) }}" class="btn btn-sm btn-light border" title="Configurar plan y suscripción">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Aún no hay colegios dados de alta.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Distribución por Plan y Accesos Rápidos --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="title fw-bold"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Distribución por Plan</span>
            </div>
            <div class="card-body">
                @foreach(['basico'=>'Básico','pro'=>'Profesional','institucional'=>'Institucional'] as $k=>$label)
                    @php $n = $byPlan[$k] ?? 0; $pct = $stats['schools'] ? round($n/$stats['schools']*100) : 0; @endphp
                    <div class="d-flex justify-content-between mb-1 small fw-bold">
                        <span>{{ $label }}</span>
                        <span>{{ $n }} ({{ $pct }}%)</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="title fw-bold"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Acciones Globales</span>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('schools.index') }}" class="btn btn-brand w-100 py-2 text-start">
                    <i class="bi bi-plus-circle me-2"></i> Dar de Alta Nuevo Colegio
                </a>
                <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary w-100 py-2 text-start">
                    <i class="bi bi-shield-check me-2"></i> Bitácora de Auditoría
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
