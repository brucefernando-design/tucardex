@extends('layouts.app')

@section('title', 'Secretaría y Control Escolar')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-workspace text-primary"></i> Secretaría y Control Escolar
            </h3>
            <p class="text-muted small mb-0">Gestión oficial de trámites, credenciales escolares, constancias con QR y documentación institucional.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('secretaria.credenciales') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-person-badge"></i>
                <span class="fw-medium">Credenciales</span>
            </a>
            <a href="{{ route('secretaria.constancias') }}" class="btn btn-primary rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-text"></i>
                <span class="fw-medium">Emitir Constancias</span>
            </a>
            <a href="{{ route('secretaria.oficios') }}" class="btn btn-outline-dark rounded-pill px-3 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-envelope-paper"></i>
                <span class="fw-medium">Citatorios / Oficios</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Alumnos Activos</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_students'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-3">
                        <i class="bi bi-collection-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Grados / Grupos</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_courses'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info fs-3">
                        <i class="bi bi-calendar2-week"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Ciclo Escolar</div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $setting->academic_year ?? '2025 - 2026' }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning-subtle text-warning fs-3">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Validación QR</div>
                        <h6 class="fw-bold mb-0 text-dark">Activa y Segura</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Central Search & Actions Hub -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-5">
                    <h5 class="fw-bold mb-1 text-dark">Búsqueda Rápida de Expediente</h5>
                    <p class="text-muted small mb-0">Localiza al estudiante para emitir de forma inmediata sus documentos oficiales.</p>
                </div>
                <div class="col-12 col-lg-7">
                    <form method="GET" action="{{ route('secretaria.index') }}" class="row g-2">
                        <div class="col-12 col-md-5">
                            <select name="course_id" class="form-select rounded-pill" onchange="this.form.submit()">
                                <option value="">-- Todos los grados/grupos --</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->full_name }} ({{ $c->level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Nombre, CURP o Matrícula..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary rounded-end-pill px-3">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                @if(request()->hasAny(['search', 'course_id']))
                                    <a href="{{ route('secretaria.index') }}" class="btn btn-outline-secondary rounded-pill ms-1" title="Limpiar filtros">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Document Issuance Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Estudiantes Registrados ({{ $students->total() }})</h6>
            <span class="badge bg-light text-dark border">Página {{ $students->currentPage() }} de {{ $students->lastPage() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase">
                        <th class="ps-4">Estudiante</th>
                        <th>Matrícula / CURP</th>
                        <th>Grado y Grupo</th>
                        <th>Tutor / Contacto</th>
                        <th class="text-end pe-4">Trámites y Documentos Rápidos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $st)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    @if($st->photo_url)
                                        <img src="{{ $st->photo_url }}" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width:40px;height:40px;font-size:14px;">
                                            {{ $st->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $st->full_name }}</div>
                                        <div class="text-muted small">{{ $st->email ?? 'Sin correo' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-dark-subtle text-dark font-monospace mb-1">{{ $st->code }}</span>
                                <div class="text-muted small">{{ $st->curp ?? $st->dni ?? 'Sin CURP' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ optional($st->course)->name }} "{{ optional($st->course)->section }}"</div>
                                <div class="text-muted small">{{ optional($st->course)->level ?? 'General' }} · {{ optional($st->course)->shift ?? 'Matutino' }}</div>
                            </td>
                            <td>
                                <div class="text-dark small fw-medium">{{ $st->guardian_name ?? 'No registrado' }}</div>
                                <div class="text-muted small">{{ $st->guardian_phone ?? $st->phone ?? 'Sin teléfono' }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('secretaria.credencial.descargar', $st->id) }}" class="btn btn-sm btn-outline-primary" title="Descargar Credencial PVC">
                                        <i class="bi bi-person-badge"></i> Credencial
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li><h6 class="dropdown-header text-uppercase small">Constancias Oficiales</h6></li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('secretaria.constancia.descargar', $st->id) }}">
                                                <i class="bi bi-file-earmark-check text-success me-2"></i> Constancia de Estudios
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('secretaria.kardex.descargar', $st->id) }}">
                                                <i class="bi bi-journal-bookmark text-primary me-2"></i> Kárdex / Calificaciones
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('secretaria.buena-conducta.descargar', $st->id) }}">
                                                <i class="bi bi-shield-check text-info me-2"></i> Carta de Buena Conducta
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('secretaria.no-adeudo.descargar', $st->id) }}">
                                                <i class="bi bi-cash-coin text-warning me-2"></i> Constancia de No Adeudo
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('secretaria.ficha.descargar', $st->id) }}">
                                                <i class="bi bi-file-person text-secondary me-2"></i> Ficha de Inscripción
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('secretaria.constancias', ['student_id' => $st->id]) }}">
                                                <i class="bi bi-sliders text-dark me-2"></i> Personalizar y Emitir...
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-1 d-block mb-2 text-secondary"></i>
                                No se encontraron estudiantes con los criterios indicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
