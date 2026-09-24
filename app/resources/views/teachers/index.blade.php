@extends('layouts.app')
@section('title', 'Docentes')

@section('content')
<div class="page-head">
    <div><h1>Docentes</h1><div class="breadcrumb-mini">Plantel docente del colegio</div></div>
    <a href="{{ route('teachers.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-person-plus"></i> Nuevo docente</a>
</div>

<div class="card">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-8"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por nombre, código o especialidad..."></div>
            <div class="col-md-2"><select name="status" class="form-select"><option value="">Todos</option><option value="activo" @selected(request('status')=='activo')>Activo</option><option value="inactivo" @selected(request('status')=='inactivo')>Inactivo</option></select></div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Filtrar</button></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th class="ps-3">Código</th><th>Docente</th><th>Especialidad</th><th>Teléfono</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
                <tbody>
                @forelse($teachers as $t)
                    <tr>
                        <td class="ps-3 text-muted">{{ $t->code }}</td>
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar-sm">{{ mb_substr($t->first_name,0,1) }}{{ mb_substr($t->last_name,0,1) }}</span><div><strong>{{ $t->full_name }}</strong><div class="small text-muted">{{ $t->email ?? '—' }}</div></div></div></td>
                        <td>{{ $t->specialty ?? '—' }}</td>
                        <td>{{ $t->phone ?? '—' }}</td>
                        <td><span class="badge-soft badge-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
                        <td class="text-end pe-3">
                            <a href="{{ route('teachers.show', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('teachers.edit', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('teachers.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar docente?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-person-badge"></i><p>No se encontraron docentes</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $teachers->links() }}
    </div>
</div>
@endsection
