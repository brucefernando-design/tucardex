@extends('layouts.app')
@section('title', 'Carga académica')

@section('content')
<div class="page-head">
    <div><h1>Carga académica</h1><div class="breadcrumb-mini">{{ $course->name }} "{{ $course->section }}" · {{ $course->level }} · Gestión {{ $course->academic_year }}</div></div>
    <a href="{{ route('courses.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-journal-bookmark"></i> Materias asignadas ({{ $course->subjects->count() }})</span></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="ps-3">Materia</th><th>Docente</th><th>Hrs/sem</th><th class="text-end pe-3">Acciones</th></tr></thead>
                    <tbody>
                    @forelse($course->subjects as $subject)
                        <tr>
                            <td class="ps-3"><strong>{{ $subject->name }}</strong><div class="small text-muted">{{ $subject->code }}</div></td>
                            <td>
                                <form action="{{ route('courses.academic.update', [$course, $subject]) }}" method="POST" class="d-flex gap-1 align-items-center">@csrf @method('PUT')
                                    <select name="teacher_id" class="form-select form-select-sm" style="min-width:150px">
                                        <option value="">Sin docente</option>
                                        @foreach($teachers as $t)<option value="{{ $t->id }}" @selected($subject->pivot->teacher_id==$t->id)>{{ $t->full_name }}</option>@endforeach
                                    </select>
                                    <input type="number" name="hours_per_week" value="{{ $subject->pivot->hours_per_week }}" min="1" max="20" class="form-control form-control-sm" style="width:64px">
                                    <button class="btn btn-sm btn-light" title="Guardar"><i class="bi bi-check2"></i></button>
                                </form>
                            </td>
                            <td>{{ $subject->pivot->hours_per_week }}</td>
                            <td class="text-end pe-3">
                                <form action="{{ route('courses.academic.destroy', [$course, $subject]) }}" method="POST" onsubmit="return confirm('¿Quitar esta materia del curso?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="bi bi-journal-x"></i><p>Aún no hay materias asignadas</p></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-accent" style="align-self:start">
        <div class="card-header"><span class="title"><i class="bi bi-plus-circle"></i> Asignar materia</span></div>
        <div class="card-body">
            @if($availableSubjects->isEmpty())
                <p class="text-muted mb-0"><i class="bi bi-check-circle text-success"></i> Todas las materias disponibles ya están asignadas a este curso.</p>
            @else
                <form action="{{ route('courses.academic.store', $course) }}" method="POST">@csrf
                    <div class="mb-3">
                        <label class="form-label">Materia <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($availableSubjects as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Docente</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }} — {{ $t->specialty }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horas por semana <span class="text-danger">*</span></label>
                        <input type="number" name="hours_per_week" value="3" min="1" max="20" class="form-control" required>
                    </div>
                    <button class="btn btn-brand btn-icon w-100"><i class="bi bi-plus-lg"></i> Asignar al curso</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
