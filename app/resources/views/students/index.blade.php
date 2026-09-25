@extends('layouts.app')
@section('title', 'Estudiantes')

@section('content')
<div class="page-head">
    <div><h1>Estudiantes</h1><div class="breadcrumb-mini">Inscripciones, expedientes y alumnos</div></div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('students.import.form') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-upload"></i> Importar</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download"></i> Exportar</button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="{{ route('students.export', array_merge(request()->only('course_id','status'), ['format'=>'csv'])) }}"><i class="bi bi-filetype-csv me-2 text-success"></i>Excel / CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('students.export', array_merge(request()->only('course_id','status'), ['format'=>'pdf'])) }}"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>PDF Lista</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-success btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-printer-fill"></i> Boletas Masivas
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2" style="min-width: 260px; max-height: 340px; overflow-y: auto;">
                <li class="dropdown-header text-uppercase small fw-bold px-2 py-1 text-muted">Boletas por Grupo (PDF)</li>
                @forelse($courses as $c)
                    <li>
                        <a class="dropdown-item py-2 px-2 rounded-2 d-flex align-items-center justify-content-between" href="{{ route('courses.boletas_masivas', $c) }}" target="_blank">
                            <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ $c->name }} "{{ $c->section }}"</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10px;">PDF</span>
                        </a>
                    </li>
                @empty
                    <li><span class="dropdown-item text-muted small">No hay grupos registrados</span></li>
                @endforelse
            </ul>
        </div>
        <a href="{{ route('students.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-person-plus"></i> Nuevo estudiante</a>
    </div>
</div>

@php $currentSchool = auth()->user()?->school; @endphp
@if($currentSchool && $currentSchool->isOnTrial())
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between py-2 px-3 mb-3 border-warning shadow-sm" style="border-radius: 12px; background: #fffbeb;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-exclamation fs-4 text-warning"></i>
            <div>
                <strong class="d-block" style="font-size:13.5px;">Modo de Prueba Gratuita (Límite: {{ $currentSchool->maxStudents() }} alumnos)</strong>
                <small class="text-muted">Tienes registrados <strong>{{ $currentSchool->students()->count() }}</strong> de <strong>{{ $currentSchool->maxStudents() }}</strong> alumnos permitidos en prueba.</small>
            </div>
        </div>
        @if($currentSchool->students()->count() >= $currentSchool->maxStudents())
            <a href="mailto:ventas@tucardex.com?subject={{ urlencode('Ampliar cupo - ' . $currentSchool->name) }}" class="btn btn-sm btn-dark text-nowrap rounded-pill px-3 py-1 mt-2 mt-md-0" style="font-size:12px;">
                <i class="bi bi-arrow-up-circle me-1"></i> Activar Plan para Alumnos Ilimitados
            </a>
        @endif
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-5"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por nombre, matrícula o CURP..."></div>
            <div class="col-md-3">
                <select name="course_id" class="form-select">
                    <option value="">Todos los grados y grupos</option>
                    @foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course_id')==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['activo','inactivo','retirado'] as $st)<option value="{{ $st }}" @selected(request('status')==$st)>{{ ucfirst($st) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Filtrar</button></div>

            @if(request('course_id'))
                @php $selectedCourse = $courses->firstWhere('id', request('course_id')); @endphp
                @if($selectedCourse)
                    <div class="col-12 mt-1">
                        <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between py-2 px-3 mb-0 rounded-3 border-success shadow-xs" style="background:#f0fdf4;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-mortarboard-fill text-success fs-5"></i>
                                <div>
                                    <strong class="text-success d-block" style="font-size:13px;">Grupo Filtrado: {{ $selectedCourse->name }} "{{ $selectedCourse->section }}"</strong>
                                    <span class="text-muted" style="font-size:11.5px;">Descarga todas las boletas de calificaciones de este grupo compiladas en un solo archivo PDF.</span>
                                </div>
                            </div>
                            <a href="{{ route('courses.boletas_masivas', $selectedCourse) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm mt-2 mt-md-0 fw-semibold">
                                <i class="bi bi-printer-fill me-1"></i> Imprimir Boletas de {{ $selectedCourse->name }} "{{ $selectedCourse->section }}" (PDF)
                            </a>
                        </div>
                    </div>
                @endif
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th class="ps-3">Matrícula</th><th>Alumno</th><th>Grado/Grupo</th><th>Padre o Tutor</th><th>Teléfono</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
                <tbody>
                @forelse($students as $s)
                    <tr>
                        <td class="ps-3 text-muted">{{ $s->code }}</td>
                        <td><div class="d-flex align-items-center gap-2">@if($s->photo_url)<img src="{{ $s->photo_url }}" class="avatar-sm" style="object-fit:cover">@else<span class="avatar-sm">{{ $s->initials }}</span>@endif<div><strong>{{ $s->full_name }}</strong><div class="small text-muted">CURP: {{ $s->curp ?? $s->dni ?? '—' }}</div></div></div></td>
                        <td>{{ optional($s->course)->name ?? '—' }}</td>
                        <td>{{ $s->guardian_name ?? '—' }}</td>
                        <td>{{ $s->guardian_phone ?? $s->phone ?? '—' }}</td>
                        <td><span class="badge-soft badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
                        <td class="text-end pe-3">
                            <a href="{{ route('students.show', $s) }}" class="btn btn-sm btn-light" title="Ver"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('students.boletin', $s) }}" class="btn btn-sm btn-light text-danger" title="Boleta de calificaciones (PDF)"><i class="bi bi-file-earmark-pdf"></i></a>
                            <a href="{{ route('students.edit', $s) }}" class="btn btn-sm btn-light" title="Editar"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('students.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar estudiante?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="Eliminar"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i><p>No se encontraron estudiantes</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $students->links() }}
    </div>
</div>
@endsection
