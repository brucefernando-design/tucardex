@extends('layouts.app')
@section('title', 'Materias')

@section('content')
<div class="page-head"><div><h1>Materias</h1><div class="breadcrumb-mini">Áreas curriculares y asignaturas</div></div>
    <a href="{{ route('subjects.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-plus-lg"></i> Nueva materia</a></div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-10"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar materia..."></div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Buscar</button></div></form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Código</th><th>Materia</th><th>Área</th><th>Cursos</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
        <tbody>
        @forelse($subjects as $s)
            <tr>
                <td class="ps-3 text-muted">{{ $s->code }}</td>
                <td><strong>{{ $s->name }}</strong></td>
                <td>{{ $s->area ?? '—' }}</td>
                <td><span class="badge bg-light text-dark border">{{ $s->courses_count }}</span></td>
                <td><span class="badge-soft badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
                <td class="text-end pe-3">
                    <a href="{{ route('subjects.edit', $s) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('subjects.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar materia?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-journal-bookmark"></i><p>No hay materias</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $subjects->links() }}
</div></div>
@endsection
