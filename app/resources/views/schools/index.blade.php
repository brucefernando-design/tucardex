@extends('layouts.app')
@section('title', 'Colegios Registrados')

@section('content')
<div class="page-head">
    <div>
        <h1>Colegios de TuKardex</h1>
        <div class="breadcrumb-mini">Gestión global de instituciones multi-inquilino y suscripciones SaaS</div>
    </div>
    <button class="btn btn-brand btn-icon shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoColegio">
        <i class="bi bi-plus-lg"></i> Dar de Alta Nuevo Colegio
    </button>
</div>

<div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6">
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar colegio por nombre o identificador...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected(request('status')=='activo')>Activos</option>
                    <option value="suspendido" @selected(request('status')=='suspendido')>Suspendidos (Pausados)</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Filtrar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Colegio</th>
                        <th>Plan</th>
                        <th>Alumnos</th>
                        <th>Cuota Mensual</th>
                        <th>Prueba / Renovación</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($schools as $s)
                    @php $sub = $s->calculateMonthlySubscription(); @endphp
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('schools.show', $s) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $s->name }}
                            </a>
                            <div class="small text-muted">{{ $s->email ?? $s->slug }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-capitalize">{{ $s->plan }}</span>
                        </td>
                        <td>
                            <strong>{{ $s->students_count }}</strong>
                        </td>
                        <td>
                            <strong class="text-success">${{ number_format($sub['total'], 2) }}</strong>
                            <small class="text-muted d-block" style="font-size:11px;">${{ number_format($sub['unit_price'], 2) }}/alumno</small>
                        </td>
                        <td class="small text-muted">
                            @if($s->isOnTrial())
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                    Prueba: {{ $s->trialDaysRemaining() }} días
                                </span>
                            @else
                                {{ optional($s->billing_renews_at)->format('d/m/Y') ?? 'Mensual' }}
                            @endif
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
                                <form action="{{ route('schools.impersonate', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Deseas ingresar al colegio {{ $s->name }} en modo soporte técnico? Podrás salir cuando gustes desde la barra superior.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Entrar como Administrador de este colegio">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                                    </button>
                                </form>
                                <a href="{{ route('schools.show', $s) }}" class="btn btn-sm btn-light border" title="Administrar suscripción y configuración">
                                    <i class="bi bi-gear"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron instituciones registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $schools->links() }}
        </div>
    </div>
</div>

<!-- Modal Dar de Alta Colegio -->
<div class="modal fade" id="modalNuevoColegio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('schools.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-buildings me-2"></i> Dar de Alta Nuevo Colegio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-success border-bottom pb-2 mb-3">1. Información de la Institución</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Nombre del Colegio *</label>
                        <input name="name" class="form-control" placeholder="Ej. Instituto Anglo Español" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Plan SaaS *</label>
                        <select name="plan" class="form-select" required>
                            <option value="basico">Básico (Hasta 200 alumnos)</option>
                            <option value="pro" selected>Profesional (Recomendado)</option>
                            <option value="institucional">Institucional (Ilimitado)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Teléfono de Contacto</label>
                        <input name="phone" class="form-control" placeholder="(867) 123-4567">
                    </div>
                </div>

                <h6 class="fw-bold text-success border-bottom pb-2 mb-3">2. Cuenta del Administrador / Director</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nombre del Director(a) *</label>
                        <input name="admin_name" class="form-control" placeholder="Ej. Lic. Fernando Morales" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Correo de Acceso (Usuario) *</label>
                        <input type="email" name="admin_email" class="form-control" placeholder="director@colegio.edu.mx" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Contraseña Inicial *</label>
                        <input type="text" name="admin_password" class="form-control font-monospace" value="Colegio{{ date('Y') }}*" required>
                        <div class="form-text">El director podrá cambiarla posteriormente desde su perfil.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Crear Colegio y Activar Licencia</button>
            </div>
        </form>
    </div>
</div>
@endsection
