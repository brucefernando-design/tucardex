@extends('layouts.app')
@section('title', 'Estudiantes')

@section('content')
<div class="page-head">
    <div><h1>Estudiantes</h1><div class="breadcrumb-mini">Inscripciones, expedientes y alumnos</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('students.import.form') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-upload"></i> Importar</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download"></i> Exportar</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('students.export', array_merge(request()->only('course_id','status'), ['format'=>'csv'])) }}"><i class="bi bi-filetype-csv me-2"></i>Excel / CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('students.export', array_merge(request()->only('course_id','status'), ['format'=>'pdf'])) }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
            </ul>
        </div>
        <a href="{{ route('students.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-person-plus"></i> Nuevo estudiante</a>
    </div>
</div>

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
