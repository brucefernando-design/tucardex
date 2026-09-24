@extends('layouts.app')
@section('title', 'Inscripciones')

@section('content')
<div class="page-head"><div><h1>Inscripciones</h1><div class="breadcrumb-mini">Inscripción de alumnos por ciclo escolar</div></div>
    <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mEnroll"><i class="bi bi-plus-lg"></i> Nueva inscripción</button></div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-4">
        <select name="course_id" class="form-select" onchange="this.form.submit()"><option value="">Todos los grupos</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course_id')==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
        </select></div></form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Estudiante</th><th>Grado/Grupo</th><th>Ciclo Escolar</th><th>Fecha</th><th>Estado</th><th class="text-end pe-3"></th></tr></thead>
        <tbody>
        @forelse($enrollments as $e)
            <tr>
                <td class="ps-3">{{ optional($e->student)->full_name }}</td>
                <td>{{ optional($e->course)->name }} "{{ optional($e->course)->section }}"</td>
                <td>{{ $e->academic_year }}</td>
                <td>{{ optional($e->enrollment_date)->format('d/m/Y') }}</td>
                <td><span class="badge-soft badge-{{ $e->status }}">{{ ucfirst($e->status) }}</span></td>
                <td class="text-end pe-3"><form action="{{ route('enrollments.destroy', $e) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-card-checklist"></i><p>Sin inscripciones registradas</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $enrollments->links() }}
</div></div>

<div class="modal fade" id="mEnroll"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('enrollments.store') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title">Nueva inscripción</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Estudiante</label><select name="student_id" class="form-select" required><option value="">Seleccione...</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->code }})</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Grado y Grupo</label><select name="course_id" class="form-select" required><option value="">Seleccione...</option>@foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->name }} "{{ $c->section }}"</option>@endforeach</select></div>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">Ciclo Escolar</label><input name="academic_year" value="{{ date('Y') }}" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Fecha</label><input type="date" name="enrollment_date" value="{{ date('Y-m-d') }}" class="form-control" required></div>
            </div>
            <input type="hidden" name="status" value="inscrito">
        </div>
        <div class="modal-footer"><button class="btn btn-brand">Guardar inscripción</button></div>
    </form>
</div></div></div>
@endsection
