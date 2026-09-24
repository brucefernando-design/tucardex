@extends('layouts.app')
@section('title', 'Detalle del colegio')

@section('content')
<div class="page-head">
    <div><h1>{{ $school->name }}</h1><div class="breadcrumb-mini">Colegios / {{ $school->slug }}</div></div>
    <a href="{{ route('schools.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="stats-row">
    <div class="stat-card bg-teal"><div class="label">Alumnos</div><div class="value">{{ $stats['students'] }}</div><i class="bi bi-people icon"></i></div>
    <div class="stat-card bg-green"><div class="label">Docentes</div><div class="value">{{ $stats['teachers'] }}</div><i class="bi bi-person-badge icon"></i></div>
    <div class="stat-card bg-blue"><div class="label">Usuarios Totales</div><div class="value">{{ $stats['users'] }}</div><i class="bi bi-person-gear icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Ingresos cobrados</div><div class="value">$ {{ number_format($stats['income'], 2) }} MXN</div><i class="bi bi-cash icon"></i></div>
</div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-gear"></i> Configuración y Estado del Colegio</span></div>
        <div class="card-body">
            <form action="{{ route('schools.update', $school) }}" method="POST">@csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nombre del Colegio</label><input name="name" value="{{ old('name', $school->name) }}" class="form-control" required></div>
                    <div class="col-md-4">
                        <label class="form-label">Plan</label>
                        <select name="plan" class="form-select">@foreach(\App\Models\School::PLANS as $k=>$v)<option value="{{ $k }}" @selected(old('plan',$school->plan)==$k)>{{ $v }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado en la Plataforma</label>
                        <select name="status" class="form-select">
                            <option value="activo" @selected($school->status=='activo')>Activo (Habilitado)</option>
                            <option value="suspendido" @selected($school->status=='suspendido')>Pausado / Suspendido</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Teléfono</label><input name="phone" value="{{ old('phone', $school->phone) }}" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Correo</label><input name="email" value="{{ old('email', $school->email) }}" class="form-control"></div>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                </div>
            </form>

            @if($school->status=='suspendido')
                <div class="alert alert-danger mt-3 mb-0"><i class="bi bi-exclamation-triangle me-1"></i> <strong>Colegio Suspendido:</strong> Ningún usuario de este colegio puede ingresar al sistema hasta que lo reactives.</div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-shield-lock"></i> Administradores del Colegio</span></div>
        <div class="card-body p-0"><table class="table mb-0"><tbody>
        @forelse($admins as $a)
            <tr><td class="ps-3"><span class="avatar-sm me-2">{{ $a->initials() }}</span>{{ $a->name }}</td><td class="text-muted">{{ $a->email }}</td></tr>
        @empty<tr><td class="empty-state">Sin administradores asignados</td></tr>@endforelse
        </tbody></table></div>
        <div class="card-body">
            <div class="small text-muted">
                <div class="mb-1"><i class="bi bi-tag me-1"></i> Plan: <strong class="text-capitalize">{{ $school->plan }}</strong></div>
                <div class="mb-1"><i class="bi bi-calendar me-1"></i> Fecha de alta: {{ $school->created_at->format('d/m/Y') }}</div>
                <div><i class="bi bi-hourglass me-1"></i> Periodo de prueba: {{ optional($school->trial_ends_at)->format('d/m/Y') ?? '—' }}</div>
            </div>

            @if($school->id !== 1)
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-danger fw-bold small">Zona de Peligro</div>
                        <div class="text-muted small">Eliminar este colegio y sus datos permanentemente.</div>
                    </div>
                    <form action="{{ route('schools.destroy', $school) }}" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar y eliminar este colegio definitivamente de la plataforma?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Cancelar Colegio</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
