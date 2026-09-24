@extends('layouts.app')
@section('title', 'Colegios')

@section('content')
<div class="page-head">
    <div>
        <h1>Colegios de TuCardex</h1>
        <div class="breadcrumb-mini">Gestión global de instituciones (tenants multi-inquilino)</div>
    </div>
    <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#modalNuevoColegio">
        <i class="bi bi-plus-lg"></i> Dar de Alta Colegio
    </button>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar colegio por nombre o identificador..."></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">Todos los estados</option><option value="activo" @selected(request('status')=='activo')>Activos</option><option value="suspendido" @selected(request('status')=='suspendido')>Suspendidos (Pausados)</option></select></div>
        <div class="col-md-3 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr>
            <th class="ps-3">Colegio</th>
            <th>Plan</th>
            <th>Usuarios</th>
            <th>Alumnos</th>
            <th>Prueba hasta</th>
            <th>Estado</th>
            <th class="text-end pe-3">Acciones</th>
        </tr></thead>
        <tbody>
        @forelse($schools as $s)
            <tr>
                <td class="ps-3"><strong>{{ $s->name }}</strong><div class="small text-muted">{{ $s->email ?? $s->slug }}</div></td>
                <td><span class="badge bg-light text-dark border text-capitalize">{{ $s->plan }}</span></td>
                <td>{{ $s->users_count }}</td>
                <td>{{ $s->students_count }}</td>
                <td class="small text-muted">{{ optional($s->trial_ends_at)->format('d/m/Y') ?? '—' }}</td>
                <td>
                    <span class="badge-soft {{ $s->status=='activo' ? 'badge-activo':'badge-vencido' }}">
                        {{ $s->status == 'activo' ? 'Activo' : 'Suspendido / Pausado' }}
                    </span>
                </td>
                <td class="text-end pe-3">
                    <a href="{{ route('schools.show', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-gear"></i> Administrar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-buildings"></i><p>No hay colegios registrados</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $schools->links() }}
</div></div>

<!-- Modal Dar de Alta Colegio -->
<div class="modal fade" id="modalNuevoColegio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('schools.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-building-add me-1"></i> Dar de Alta Nuevo Colegio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre del Colegio *</label>
                    <input name="name" class="form-control" placeholder="Ej: Instituto Hidalgo" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Plan *</label>
                    <select name="plan" class="form-select">
                        <option value="basico">Básico</option>
                        <option value="pro" selected>Profesional</option>
                        <option value="institucional">Institucional</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono del Colegio</label>
                    <input name="phone" class="form-control" placeholder="Ej: 55 1234 5678">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre del Director / Admin *</label>
                    <input name="admin_name" class="form-control" placeholder="Ej: Lic. Roberto García" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Correo Electrónico de Acceso *</label>
                    <input name="admin_email" type="email" class="form-control" placeholder="director@colegio.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contraseña de Acceso *</label>
                    <input name="admin_password" type="text" class="form-control" value="Colegio2026!" required>
                    <div class="form-text">Contraseña inicial generada para el director.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Crear Colegio</button>
            </div>
        </form>
    </div>
</div>
@endsection
