@extends('layouts.app')
@section('title', 'Detalle del curso')

@section('content')
<div class="page-head"><div><h1>{{ $course->name }} "{{ $course->section }}"</h1><div class="breadcrumb-mini">{{ $course->level }} · Turno {{ $course->shift }} · Gestión {{ $course->academic_year }}</div></div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-danger btn-icon dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-pdf"></i> Acta de notas</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('courses.acta', $course) }}"><i class="bi bi-table me-2"></i>Todos los períodos</a></li>
                @foreach(['1er Trimestre','2do Trimestre','3er Trimestre','Final'] as $p)
                    <li><a class="dropdown-item" href="{{ route('courses.acta', ['course'=>$course,'period'=>$p]) }}">{{ $p }}</a></li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('courses.academic.index', $course) }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-journal-bookmark"></i> Carga académica</a>
        <a href="{{ route('courses.edit', $course) }}" class="btn btn-brand btn-icon"><i class="bi bi-pencil"></i> Editar</a>
        <a href="{{ route('courses.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
    </div></div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-people"></i> Estudiantes ({{ $course->students->count() }})</span></div>
        <div class="card-body p-0"><table class="table mb-0"><tbody>
        @forelse($course->students as $s)
            <tr><td class="ps-3"><span class="avatar-sm me-2">{{ mb_substr($s->first_name,0,1) }}{{ mb_substr($s->last_name,0,1) }}</span>{{ $s->full_name }}</td><td class="text-muted">{{ $s->code }}</td></tr>
        @empty<tr><td class="empty-state">Sin estudiantes asignados</td></tr>@endforelse
        </tbody></table></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-journal-bookmark"></i> Materias</span></div>
        <div class="card-body p-0"><table class="table mb-0"><tbody>
        @forelse($course->subjects as $sub)
            <tr><td class="ps-3">{{ $sub->name }}</td><td class="text-muted">{{ $sub->pivot->hours_per_week }} hrs/sem</td></tr>
        @empty<tr><td class="empty-state">Sin materias asignadas</td></tr>@endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
