@extends('layouts.app')
@section('title', 'Plataforma')

@section('content')
<div style="position:relative;overflow:hidden;border-radius:var(--radius);padding:26px 28px;margin-bottom:22px;
            background:linear-gradient(120deg,#0f1f18,#15803d 120%);color:#fff;box-shadow:var(--shadow)">
    <div style="position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06)"></div>
    <div style="position:relative;z-index:1">
        <div style="font-size:13px;opacity:.85">Panel de la plataforma</div>
        <h1 style="font-size:24px;font-weight:800;margin:6px 0 4px">Hola, {{ explode(' ', auth()->user()->name)[0] }} 🛰️</h1>
        <div style="opacity:.9;font-size:13.5px">Administración global de colegios (multi-tenant)</div>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card bg-teal"><div class="label">Colegios</div><div class="value">{{ $stats['schools'] }}</div><i class="bi bi-buildings icon"></i>
        <div class="foot"><span>Registrados</span><a href="{{ route('schools.index') }}" class="text-white">Ver todos <i class="bi bi-arrow-right"></i></a></div></div>
    <div class="stat-card bg-green"><div class="label">Activos</div><div class="value">{{ $stats['active'] }}</div><i class="bi bi-check-circle icon"></i>
        <div class="foot"><span>En servicio</span><span>{{ $stats['schools'] ? round($stats['active']/$stats['schools']*100) : 0 }}%</span></div></div>
    <div class="stat-card bg-blue"><div class="label">Estudiantes (global)</div><div class="value">{{ number_format($stats['students']) }}</div><i class="bi bi-people icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Usuarios (global)</div><div class="value">{{ number_format($stats['users']) }}</div><i class="bi bi-person-gear icon"></i></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-buildings"></i> Colegios recientes</span><a href="{{ route('schools.index') }}" class="small text-muted">Ver todos</a></div>
        <div class="card-body p-0"><table class="table table-hover align-middle mb-0">
            <thead><tr><th class="ps-3">Colegio</th><th>Plan</th><th>Estudiantes</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($recentSchools as $s)
                <tr style="cursor:pointer" onclick="window.location='{{ route('schools.show', $s) }}'">
                    <td class="ps-3"><strong>{{ $s->name }}</strong><div class="small text-muted">{{ $s->slug }}</div></td>
                    <td><span class="badge bg-light text-dark border text-capitalize">{{ $s->plan }}</span></td>
                    <td>{{ $s->students_count }}</td>
                    <td><span class="badge-soft {{ $s->status=='activo' ? 'badge-activo':'badge-vencido' }}">{{ ucfirst($s->status) }}</span></td>
                </tr>
            @empty<tr><td colspan="4" class="empty-state">Aún no hay colegios</td></tr>@endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-pie-chart"></i> Por plan</span></div>
        <div class="card-body">
            @foreach(['basico'=>'Básico','pro'=>'Profesional','institucional'=>'Institucional'] as $k=>$label)
                @php $n = $byPlan[$k] ?? 0; $pct = $stats['schools'] ? round($n/$stats['schools']*100) : 0; @endphp
                <div class="d-flex justify-content-between mb-1"><span>{{ $label }}</span><span>{{ $n }}</span></div>
                <div class="progress mb-3"><div class="progress-bar" style="background:var(--brand);width:{{ $pct }}%"></div></div>
            @endforeach
        </div>
    </div>
</div>
@endsection
