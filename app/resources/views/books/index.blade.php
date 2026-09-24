@extends('layouts.app')
@section('title', 'Biblioteca · Libros')

@section('content')
<div class="page-head">
    <div><h1>Catálogo de libros</h1><div class="breadcrumb-mini">Inventario de la biblioteca</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-arrow-left-right"></i> Préstamos</a>
        <a href="{{ route('books.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-plus-lg"></i> Nuevo libro</a>
    </div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por título, autor o ISBN..."></div>
        <div class="col-md-3"><select name="category" class="form-select"><option value="">Todas las categorías</option>@foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')==$c)>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Buscar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Título</th><th>Autor</th><th>Categoría</th><th>Ubicación</th><th>Disponibles</th><th class="text-end pe-3">Acciones</th></tr></thead>
        <tbody>
        @forelse($books as $b)
            <tr>
                <td class="ps-3"><strong>{{ $b->title }}</strong><div class="small text-muted">{{ $b->isbn ? 'ISBN '.$b->isbn : '' }} {{ $b->editorial }}</div></td>
                <td>{{ $b->author ?? '—' }}</td>
                <td>@if($b->category)<span class="badge bg-light text-dark border">{{ $b->category }}</span>@else—@endif</td>
                <td>{{ $b->location ?? '—' }}</td>
                <td><span class="badge-soft {{ $b->available>0 ? 'badge-activo':'badge-vencido' }}">{{ $b->available }} / {{ $b->quantity }}</span></td>
                <td class="text-end pe-3">
                    <a href="{{ route('books.edit', $b) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('books.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar libro?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-book"></i><p>No hay libros en el catálogo</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $books->links() }}
</div></div>
@endsection
