@extends('layouts.app')
@section('title', 'Calificaciones')

@section('content')
<div class="page-head"><div><h1>Calificaciones</h1><div class="breadcrumb-mini">Registro de calificaciones por asignatura y periodo</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('grades.batch') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-table"></i> Planilla masiva</a>
        <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mGrade"><i class="bi bi-plus-lg"></i> Registrar nota</button>
    </div></div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-3"><select name="course_id" class="form-select"><option value="">Todos los cursos</option>@foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="subject_id" class="form-select"><option value="">Todas las materias</option>@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(request('subject_id')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="period" class="form-select"><option value="">Todos los periodos</option>@foreach(['1er Trimestre','2do Trimestre','3er Trimestre','Final'] as $p)<option value="{{ $p }}" @selected(request('period')==$p)>{{ $p }}</option>@endforeach</select></div>
        <div class="col-md-3 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Estudiante</th><th>Materia</th><th>Periodo</th><th>Tipo</th><th>Nota</th><th class="text-end pe-3"></th></tr></thead>
        <tbody>
        @forelse($grades as $g)
            <tr>
                <td class="ps-3">{{ optional($g->student)->full_name }}</td>
                <td>{{ optional($g->subject)->name }}</td>
                <td class="text-muted">{{ $g->period }}</td>
                <td>{{ ucfirst($g->type) }}</td>
                <td><span class="badge-soft {{ $g->score>=6.0 ? 'badge-activo' : 'badge-vencido' }}">{{ $g->score }}</span></td>
                <td class="text-end pe-3"><form action="{{ route('grades.destroy', $g) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-clipboard-data"></i><p>Sin calificaciones</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $grades->links() }}
</div></div>

<div class="modal fade" id="mGrade"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('grades.store') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title">Registrar calificación</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Estudiante</label><select name="student_id" class="form-select" required><option value="">Seleccione...</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach</select></div>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">Materia</label><select name="subject_id" class="form-select" required>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
                <div class="col-6"><label class="form-label">Curso</label><select name="course_id" class="form-select"><option value="">—</option>@foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-6"><label class="form-label">Periodo</label><select name="period" class="form-select">@foreach(['1er Trimestre','2do Trimestre','3er Trimestre','Final'] as $p)<option value="{{ $p }}">{{ $p }}</option>@endforeach</select></div>
                <div class="col-6"><label class="form-label">Tipo</label><select name="type" class="form-select">@foreach(['examen','practica','tarea','proyecto','actitudinal'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Calificación SEP (5.0 a 10.0)</label><input type="number" step="0.1" min="5" max="10" name="score" class="form-control" placeholder="Ej: 8.5" required><div class="form-text">Mínimo aprobatorio: 6.0</div></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand">Guardar nota</button></div>
    </form>
</div></div></div>
@endsection
