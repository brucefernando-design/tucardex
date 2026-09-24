@extends('layouts.app')
@section('title', 'Grados y Grupos')

@section('content')
<div class="page-head">
    <div><h1>Grados y Grupos</h1><div class="breadcrumb-mini">Grados, grupos y profesores titulares</div></div>
    <a href="{{ route('courses.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-plus-lg"></i> Nuevo grado/grupo</a>
</div>

<div class="grid-3">
    @forelse($courses as $c)
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">{{ $c->name }} <span class="text-muted">"{{ $c->section }}"</span></h5>
                        <span class="badge-soft badge-{{ $c->status }}">{{ $c->level }}</span>
                        <span class="badge bg-light text-dark border">{{ $c->shift }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('courses.show', $c) }}"><i class="bi bi-eye me-2"></i>Ver</a></li>
                            <li><a class="dropdown-item" href="{{ route('courses.academic.index', $c) }}"><i class="bi bi-journal-bookmark me-2"></i>Carga académica</a></li>
                            <li><a class="dropdown-item" href="{{ route('courses.edit', $c) }}"><i class="bi bi-pencil me-2"></i>Editar</a></li>
                            <li><form action="{{ route('courses.destroy', $c) }}" method="POST" onsubmit="return confirm('¿Eliminar curso?')">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Eliminar</button></form></li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between small text-muted mb-1"><span>Estudiantes</span><span>{{ $c->students_count }} / {{ $c->capacity }}</span></div>
                <div class="progress mb-3"><div class="progress-bar" style="background:var(--brand);width:{{ min(100, $c->capacity ? round($c->students_count/$c->capacity*100) : 0) }}%"></div></div>
                <div class="small"><i class="bi bi-person-badge text-muted me-1"></i> Profesor Titular: {{ optional($c->tutor)->full_name ?? 'Sin asignar' }}</div>
            </div>
        </div>
    @empty
        <div class="card"><div class="card-body empty-state"><i class="bi bi-collection"></i><p>No hay grados o grupos registrados</p></div></div>
    @endforelse
</div>
{{ $courses->links() }}
@endsection
