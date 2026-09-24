@extends('layouts.app')
@section('title', 'Tareas')

@section('content')
<div class="page-head">
    <div><h1>Tareas / Asignaciones</h1><div class="breadcrumb-mini">Trabajos y deberes por curso y materia</div></div>
    <a href="{{ route('assignments.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-plus-lg"></i> Nueva tarea</a>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-4"><select name="course_id" class="form-select"><option value="">Todos los cursos</option>@foreach($courses as $c)<option value="{{ $c->id }}" @selected(request('course_id')==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach</select></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">Todas</option><option value="activa" @selected(request('status')=='activa')>Activas</option><option value="cerrada" @selected(request('status')=='cerrada')>Cerradas</option></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Tarea</th><th>Curso</th><th>Materia</th><th>Entrega</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
        <tbody>
        @forelse($assignments as $a)
            <tr>
                <td class="ps-3"><strong>{{ $a->title }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($a->description, 50) }}</div></td>
                <td>{{ optional($a->course)->name }} "{{ optional($a->course)->section }}"</td>
                <td>{{ optional($a->subject)->name }}</td>
                <td>
                    {{ $a->due_date->format('d/m/Y') }}
                    @if($a->is_overdue)<span class="badge-soft badge-vencido ms-1">Vencida</span>@endif
                </td>
                <td><span class="badge-soft {{ $a->status=='activa' ? 'badge-activo':'badge-inactivo' }}">{{ ucfirst($a->status) }}</span></td>
                <td class="text-end pe-3">
                    <a href="{{ route('submissions.index', $a) }}" class="btn btn-sm btn-light" title="Entregas"><i class="bi bi-inbox"></i></a>
                    <a href="{{ route('assignments.edit', $a) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('assignments.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar tarea?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-journal-text"></i><p>No hay tareas registradas</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $assignments->links() }}
</div></div>
@endsection
